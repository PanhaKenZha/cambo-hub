<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_forms_but_cannot_access_dashboard(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in to your account');
        $this->get('/register')->assertOk()->assertSee('Create your account');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_registration_hashes_password_and_signs_in_user(): void
    {
        $this->post('/register', [
            'name' => 'Test User', 'email' => 'test@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');
        $user = User::firstOrFail();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertRedirect('/verify-email');
    }

    public function test_registration_rejects_duplicate_email_and_mismatched_password(): void
    {
        $user = User::factory()->create();
        $this->post('/register', [
            'name' => 'Another User', 'email' => $user->email,
            'password' => 'password123', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123', 'remember' => '1'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/login')->assertRedirect('/dashboard');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'missing@example.com', 'password' => 'incorrect']);
        }
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'incorrect'])
            ->assertStatus(429);
    }
}
