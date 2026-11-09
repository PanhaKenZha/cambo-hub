<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\PlatformModule;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\LoginHistory;
use App\Models\User;
use App\Notifications\SuspiciousLoginDetected;
use App\Services\PlatformSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModuleAndMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_turned_off_module_routes_return_not_found(): void
    {
        Route::middleware(['web', 'module:jobs'])->get('/test-jobs', fn () => 'Jobs page');

        $this->get('/test-jobs')->assertOk()->assertSee('Jobs page');

        $this->setSetting(PlatformModule::Jobs->settingKey(), false);

        $this->get('/test-jobs')->assertNotFound();
    }

    public function test_module_blade_directive_follows_the_switch(): void
    {
        $template = "@module('news')News is on @endmodule";

        $this->assertSame('News is on', trim(Blade::render($template)));

        $this->setSetting(PlatformModule::News->settingKey(), false);

        $this->assertSame('', trim(Blade::render($template)));
    }

    public function test_maintenance_mode_closes_public_site_but_not_admin_panel(): void
    {
        $this->setSetting('maintenance.enabled', true);
        $this->setSetting('maintenance.message', 'Upgrading servers until 6 PM.');

        $this->get('/login')->assertStatus(503)->assertSee('Upgrading servers until 6 PM.');
        $this->get('/admin/login')->assertOk();
        $this->get('/up')->assertOk();

        $admin = User::factory()->withTwoFactor()->admin(AdminRole::SuperAdmin)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);
        $this->get('/dashboard')->assertOk();
        $this->get('/admin')->assertOk()->assertSee('Maintenance mode is on');

        $this->actingAs(User::factory()->create())->get('/dashboard')->assertStatus(503);
    }

    public function test_closed_registration_blocks_sign_up(): void
    {
        $this->setSetting('registration.enabled', false);
        $this->setSetting('registration.closed_message', 'Sign-ups reopen on Monday.');

        $this->get('/register')->assertRedirect('/login');
        $this->post('/register', [
            'name' => 'New Person', 'email' => 'new@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/login')->assertSessionHas('status', 'Sign-ups reopen on Monday.');

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_login_alerts_setting_controls_new_ip_emails(): void
    {
        Notification::fake();
        $this->setSetting('notifications.login_alerts', false);
        $user = User::factory()->create(['password' => 'password123']);
        LoginHistory::factory()->create(['user_id' => $user->id, 'email' => $user->email, 'ip_address' => '203.0.113.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'ip_address' => '198.51.100.7', 'is_suspicious' => true]);
        Notification::assertNotSentTo($user, SuspiciousLoginDetected::class);
    }

    private function setSetting(string $key, mixed $value): void
    {
        [$group, $field] = explode('.', $key, 2);
        $settings = app(PlatformSettings::class);
        $settings->update($group, array_merge($settings->group($group), [$field => $value]));
    }
}
