<?php

namespace Database\Factories;

use App\Enums\HomepageSectionType;
use App\Models\HomepageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSection>
 */
class HomepageSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => HomepageSectionType::PromoBlock,
            'title' => ['en' => fake()->sentence(3), 'km' => null],
            'item_count' => 6,
            'is_visible' => true,
            'sort_order' => 0,
        ];
    }
}
