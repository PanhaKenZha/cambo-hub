<?php

namespace Database\Factories;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'placement' => BannerPlacement::HomepageHero,
            'title' => ['en' => fake()->sentence(4), 'km' => null],
            'link_url' => fake()->url(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
