<?php

namespace Database\Factories;

use App\Enums\CmsContentType;
use App\Models\CmsContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsContent>
 */
class CmsContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_type' => CmsContentType::News,
            'title' => fake()->sentence(5),
            'summary' => fake()->sentence(14),
            'image_path' => null,
            'link_url' => null,
            'badge' => null,
            'meta' => [],
            'is_featured' => false,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'starts_at' => null,
            'ends_at' => null,
            'sort_order' => 0,
        ];
    }

    public function ofType(CmsContentType $type): static
    {
        return $this->state(fn (array $attributes): array => ['content_type' => $type]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => ['ends_at' => now()->subHour()]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => ['starts_at' => now()->addDay()]);
    }
}
