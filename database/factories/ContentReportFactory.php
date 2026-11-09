<?php

namespace Database\Factories;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\ContentReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentReport>
 */
class ContentReportFactory extends Factory
{
    /**
     * Define the model's default state. Reports a user profile by default.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reportable_type' => (new User)->getMorphClass(),
            'reportable_id' => User::factory(),
            'module' => null,
            'title' => fake()->name(),
            'reporter_id' => User::factory(),
            'reason' => fake()->randomElement(ReportReason::cases()),
            'details' => fake()->sentence(),
            'status' => ReportStatus::Open,
        ];
    }
}
