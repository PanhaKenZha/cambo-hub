<?php

namespace Database\Factories;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginHistory>
 */
class LoginHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fn (array $attributes) => User::find($attributes['user_id'])?->email ?? fake()->safeEmail(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'successful' => true,
            'is_suspicious' => false,
            'failure_reason' => null,
        ];
    }

    /**
     * Indicate that the login attempt failed.
     */
    public function failed(string $reason = 'invalid_credentials'): static
    {
        return $this->state(fn (array $attributes) => [
            'successful' => false,
            'failure_reason' => $reason,
        ]);
    }
}
