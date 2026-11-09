<?php

namespace Database\Factories;

use App\Enums\AppealStatus;
use App\Models\ModerationAppeal;
use App\Models\User;
use App\Models\UserViolation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationAppeal>
 */
class ModerationAppealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appealable_type' => (new UserViolation)->getMorphClass(),
            'appealable_id' => UserViolation::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'status' => AppealStatus::Pending,
        ];
    }
}
