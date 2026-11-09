<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientAdminSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_do_not_expose_admin_navigation(): void
    {
        foreach (['/mission', '/vision', '/login', '/register'] as $path) {
            $this->get($path)->assertOk()->assertDontSee(url('/admin'));
        }
    }

    public function test_client_login_ignores_a_previous_admin_destination(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->get('/admin')->assertRedirect('/admin/login');

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertDontSee('Go to admin panel');
        foreach (['/admin', '/admin/users', '/admin/roles', '/admin/audit-logs', '/admin/two-factor/setup'] as $path) {
            $this->get($path)->assertForbidden();
        }
    }

    public function test_administrator_credentials_do_not_sign_in_through_client_login(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->admin()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);

        $this->assertGuest();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect('/admin/two-factor');
    }

    public function test_public_registration_cannot_assign_an_admin_role(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->post('/register', [
            'name' => 'Client User', 'email' => 'client@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'super-admin', 'roles' => ['super-admin'],
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'client@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->get('/admin')->assertForbidden();
    }
}
