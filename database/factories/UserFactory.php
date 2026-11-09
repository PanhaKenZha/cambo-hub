<?php

namespace Database\Factories;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign an administrator role. Roles must already be seeded.
     */
    public function admin(AdminRole $role = AdminRole::SuperAdmin): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role->value));
    }

    /**
     * Give the user confirmed two-factor authentication.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_recovery_codes' => [Str::random(10).'-'.Str::random(10)],
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the account is suspended.
     */
    public function suspended(string $reason = 'Policy violation'): static
    {
        return $this->state(fn (array $attributes) => [
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);
    }
}
