<?php

namespace Database\Factories;

use App\Models\NewsAuthor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsAuthor>
 */
class NewsAuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'bio' => ['en' => fake()->sentence(), 'km' => null],
            'is_active' => true,
        ];
    }
}
