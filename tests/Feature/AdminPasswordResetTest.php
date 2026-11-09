<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    }

    public function test_admin_login_links_to_admin_forgot_password_page(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee(route('admin.password.request'), false);
        $this->get('/admin/forgot-password')->assertOk()->assertSee('Forgot your password?');
    }

    public function test_admin_reset_email_links_to_the_admin_reset_page(): void
    {
        $admin = User::factory()->admin(AdminRole::NewsAdmin)->create();

        $this->post('/admin/forgot-password', ['email' => $admin->email])->assertSessionHas('status');

        Notification::assertSentTo($admin, ResetPassword::class, fn (ResetPassword $notification): bool => str_contains($notification->toMail($admin)->actionUrl, '/admin/reset-password/'));
    }

    public function test_regular_users_keep_the_public_reset_page_and_get_no_admin_emails(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', 'If an administrator account exists for that email, a password reset link has been sent.');
        Notification::assertNothingSent();

        $this->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentTo($user, ResetPassword::class, fn (ResetPassword $notification): bool => ! str_contains($notification->toMail($user)->actionUrl, '/admin/'));
    }

    public function test_admin_sets_a_strong_password_and_returns_to_admin_sign_in(): void
    {
        $admin = User::factory()->admin()->create();
        $token = Password::createToken($admin);

        $this->get("/admin/reset-password/{$token}?email={$admin->email}")->assertOk()->assertSee('Choose a new password');

        $this->post('/admin/reset-password', ['token' => $token, 'email' => $admin->email, 'password' => 'short1', 'password_confirmation' => 'short1'])
            ->assertSessionHasErrors('password');

        $this->post('/admin/reset-password', ['token' => $token, 'email' => $admin->email, 'password' => 'NewSecurePass2026', 'password_confirmation' => 'NewSecurePass2026'])
            ->assertRedirect('/admin/login')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewSecurePass2026', $admin->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.password_reset', 'user_id' => $admin->id]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/admin/reset-password', ['token' => 'wrong', 'email' => $admin->email, 'password' => 'NewSecurePass2026', 'password_confirmation' => 'NewSecurePass2026'])
            ->assertSessionHasErrors('email');
    }

    public function test_new_administrator_invitation_links_to_admin_reset_page(): void
    {
        $superAdmin = User::factory()->withTwoFactor()->admin()->create();
        $this->actingAs($superAdmin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        $this->post('/admin/users', ['name' => 'New Admin', 'email' => 'new.admin@example.com', 'role' => AdminRole::JobsAdmin->value]);

        $newAdmin = User::where('email', 'new.admin@example.com')->firstOrFail();
        Notification::assertSentTo($newAdmin, ResetPassword::class, fn (ResetPassword $notification): bool => str_contains($notification->toMail($newAdmin)->actionUrl, '/admin/reset-password/'));
    }
}
