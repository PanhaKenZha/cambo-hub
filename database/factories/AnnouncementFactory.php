<?php

namespace Database\Factories;

use App\Enums\AnnouncementLevel;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message' => ['en' => fake()->sentence(), 'km' => null],
            'level' => AnnouncementLevel::Info,
            'is_active' => true,
            'is_dismissible' => true,
        ];
    }
}
