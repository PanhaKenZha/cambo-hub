<?php

namespace Database\Factories;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => MenuLocation::Header,
            'label' => ['en' => ucfirst(fake()->word()), 'km' => null],
            'url' => fake()->url(),
            'is_visible' => true,
            'sort_order' => 0,
        ];
    }
}
