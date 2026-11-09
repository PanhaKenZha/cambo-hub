<?php

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Enums\PlatformModule;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalRequest>
 */
class ApprovalRequestFactory extends Factory
{
    /**
     * Define the model's default state. The approvable defaults to a user record
     * as a stand-in until content modules exist.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approvable_type' => (new User)->getMorphClass(),
            'approvable_id' => User::factory(),
            'module' => fake()->randomElement(PlatformModule::cases()),
            'title' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'status' => ApprovalStatus::Pending,
            'submitted_by' => User::factory(),
        ];
    }

    public function rejected(string $reason = 'Does not meet the publishing guidelines.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::Rejected,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
