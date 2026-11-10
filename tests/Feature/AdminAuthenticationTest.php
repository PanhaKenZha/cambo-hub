<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use App\Notifications\SuspiciousLoginDetected;
use App\Services\TwoFactorAuthenticator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Sign in to the admin panel');
    }

    public function test_non_admin_cannot_sign_in_to_admin_panel(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'successful' => false, 'failure_reason' => 'not_admin']);
    }

    public function test_non_admin_signed_in_on_public_site_is_forbidden_from_admin_area(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_without_two_factor_must_enroll_before_reaching_dashboard(): void
    {
        $admin = User::factory()->admin(AdminRole::NewsAdmin)->create(['password' => 'password123']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect('/admin/two-factor');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertRedirect('/admin/two-factor');
        $this->get('/admin/two-factor')->assertOk()->assertSee('make your account extra secure', false);

        $this->get('/admin/two-factor/setup')->assertOk()->assertSee('Set up two-factor authentication');
        $secret = session('admin.two_factor_setup_secret');

        $this->post('/admin/two-factor/setup', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/admin/two-factor/setup', ['code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertOk()
            ->assertSee('Save your recovery codes');

        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactorEnabled());
        $this->assertCount(8, $admin->two_factor_recovery_codes);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'auth.two_factor_enabled']);
        $this->get('/admin')->assertOk();
    }

    public function test_admin_can_skip_two_factor_setup_when_allowed(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password123']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123']);

        $this->get('/admin/two-factor')->assertOk()->assertSee('Skip for now');
        $this->post('/admin/two-factor/setup/skip')->assertRedirect('/admin');

        $this->get('/admin')->assertOk()->assertSee('Two-factor authentication is not set up');
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'auth.two_factor_skipped']);
    }

    public function test_two_factor_setup_cannot_be_skipped_when_disabled(): void
    {
        config(['admin.two_factor_skippable' => false]);
        $admin = User::factory()->admin()->create(['password' => 'password123']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123']);

        $this->get('/admin/two-factor')->assertOk()->assertDontSee('Skip for now')->assertSee('required for all administrators');
        $this->post('/admin/two-factor/setup/skip')->assertForbidden();
        $this->withSession([EnsureAdminSession::SKIPPED_SETUP_SESSION_KEY => true])
            ->get('/admin')
            ->assertRedirect('/admin/two-factor');
    }

    public function test_skipping_setup_does_not_bypass_challenge_for_admin_with_two_factor(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();

        $this->actingAs($admin)
            ->withSession([EnsureAdminSession::SKIPPED_SETUP_SESSION_KEY => true])
            ->get('/admin')
            ->assertRedirect('/admin/two-factor-challenge');
    }

    public function test_admin_with_two_factor_signs_in_with_authenticator_code(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create(['password' => 'password123']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect('/admin/two-factor-challenge');
        $this->assertGuest();

        $this->post('/admin/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post('/admin/two-factor-challenge', ['code' => (new Google2FA)->getCurrentOtp($admin->two_factor_secret)])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('Recent admin activity');
        $this->assertDatabaseHas('login_histories', ['user_id' => $admin->id, 'successful' => true]);
        $this->assertDatabaseHas('login_histories', ['user_id' => $admin->id, 'failure_reason' => 'invalid_two_factor_code']);
    }

    public function test_authenticator_code_cannot_be_replayed(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();
        $twoFactor = app(TwoFactorAuthenticator::class);
        $code = (new Google2FA)->getCurrentOtp($admin->two_factor_secret);

        $this->assertTrue($twoFactor->verifyCode($admin->two_factor_secret, $code, $admin->id));
        $this->assertFalse($twoFactor->verifyCode($admin->two_factor_secret, $code, $admin->id));
    }

    public function test_recovery_code_signs_in_once_and_is_audited(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create(['password' => 'password123']);
        $recoveryCode = $admin->two_factor_recovery_codes[0];

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123']);
        $this->post('/admin/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->assertSame([], $admin->refresh()->two_factor_recovery_codes);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'auth.recovery_code_used']);

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123']);
        $this->post('/admin/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertSessionHasErrors('recovery_code');
        $this->assertGuest();
    }

    public function test_admin_signing_in_on_public_login_is_rejected(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_signed_in_through_public_site_must_still_pass_two_factor(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/two-factor-challenge');

        $this->post('/admin/two-factor-challenge', ['code' => (new Google2FA)->getCurrentOtp($admin->two_factor_secret)])
            ->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
    }

    public function test_suspended_admin_cannot_sign_in(): void
    {
        $admin = User::factory()->withTwoFactor()->suspended()->admin()->create(['password' => 'password123']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertSessionHasErrors(['email' => 'This account has been suspended.']);
        $this->assertGuest();
        $this->assertNull(session(AdminAuthController::PENDING_USER_SESSION_KEY));
    }

    public function test_admin_suspended_during_session_is_signed_out(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);
        $this->get('/admin')->assertOk();

        $admin->forceFill(['suspended_at' => now()])->save();

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_account_is_locked_after_repeated_failed_attempts(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create(['password' => 'password123']);
        LoginHistory::factory()->count(5)->failed()->create(['user_id' => $admin->id, 'email' => $admin->email]);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->assertNull(session(AdminAuthController::PENDING_USER_SESSION_KEY));
        $this->assertDatabaseHas('login_histories', ['email' => $admin->email, 'failure_reason' => 'locked_out']);
    }

    public function test_idle_admin_session_is_signed_out(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();

        $this->actingAs($admin)
            ->withSession([
                EnsureAdminSession::VERIFIED_SESSION_KEY => true,
                EnsureAdminSession::LAST_ACTIVITY_SESSION_KEY => now()->subMinutes(config('admin.idle_timeout_minutes') + 1)->getTimestamp(),
            ])
            ->get('/admin')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    public function test_sign_in_from_new_ip_address_is_flagged_and_reported(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'password123']);
        LoginHistory::factory()->create(['user_id' => $user->id, 'email' => $user->email, 'ip_address' => '203.0.113.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'ip_address' => '198.51.100.7', 'is_suspicious' => true]);
        $this->assertSame('198.51.100.7', $user->refresh()->last_login_ip);
        Notification::assertSentTo($user, SuspiciousLoginDetected::class);
        $this->assertTrue(AuditLog::where('action', 'auth.suspicious_login')->exists());
    }

    public function test_first_sign_in_is_not_flagged_as_suspicious(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'is_suspicious' => false]);
        Notification::assertNothingSent();
    }

    public function test_suspended_user_cannot_sign_in_on_public_site_and_is_signed_out(): void
    {
        $user = User::factory()->suspended()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect('/account/suspended');
        $this->assertGuest();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_can_sign_out(): void
    {
        $admin = User::factory()->withTwoFactor()->admin()->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        $this->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
