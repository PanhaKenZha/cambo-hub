<?php

namespace Database\Factories;

use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Models\User;
use App\Models\UserViolation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserViolation>
 */
class UserViolationFactory extends Factory
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
            'issued_by' => User::factory(),
            'type' => ViolationType::Warning,
            'reason' => fake()->sentence(),
            'status' => ViolationStatus::Active,
        ];
    }

    public function suspension(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ViolationType::Suspension,
            'expires_at' => now()->addDays($days),
        ]);
    }
}
