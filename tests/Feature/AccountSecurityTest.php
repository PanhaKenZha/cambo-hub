<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_and_signed_link_verifies_email(): void
    {
        Notification::fake();
        $this->post('/register', ['name' => 'New User', 'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123']);
        $user = User::firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/verify-email')->assertOk();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect('/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/dashboard')->assertOk();
    }

    public function test_invalid_or_expired_verification_links_are_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertForbidden();
        $this->get('/verify-email/'.$user->id.'/'.sha1($user->email))->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_password_recovery_sends_link_and_token_can_only_be_used_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
        $data = ['token' => $token, 'email' => $user->email, 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123'];
        $this->post('/reset-password', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_invalid_and_expired_reset_tokens_cannot_change_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $this->travel(61)->minutes();
        foreach ([$token, 'invalid-token'] as $invalidToken) {
            $this->post('/reset-password', ['token' => $invalidToken, 'email' => $user->email, 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123'])
                ->assertSessionHasErrors('email');
        }
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_profile_email_change_requires_password_and_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->get('/account')->assertOk();
        $data = ['name' => 'Updated Name', 'email' => 'updated@example.com'];
        $this->patch('/account', $data)->assertSessionHasErrors('current_password');
        $this->assertNotEquals('updated@example.com', $user->fresh()->email);
        $this->patch('/account', [...$data, 'current_password' => 'password'])->assertSessionHas('status');
        $this->assertEquals('updated@example.com', $user->fresh()->email);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/dashboard')->assertRedirect('/verify-email');
    }

    public function test_changing_password_requires_current_password(): void
    {
        $user = User::factory()->create();
        $data = ['password' => 'newpassword123', 'password_confirmation' => 'newpassword123'];
        $this->actingAs($user)->put('/account/password', [...$data, 'current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password', null, 'password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->put('/account/password', [...$data, 'current_password' => 'password'])->assertSessionHas('status');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_deleting_account_requires_password_and_logs_user_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->delete('/account', ['password' => 'wrong'])->assertSessionHasErrors('password', null, 'deletion');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->delete('/account', ['password' => 'password'])->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_guests_cannot_modify_accounts(): void
    {
        $this->patch('/account', [])->assertRedirect('/login');
        $this->put('/account/password', [])->assertRedirect('/login');
        $this->delete('/account', [])->assertRedirect('/login');
    }
}
