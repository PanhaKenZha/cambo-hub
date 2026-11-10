<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\NewsArticle;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsArticle>
 */
class NewsArticleFactory extends Factory
{
    /**
     * Define the model's default state: a published article.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'news_category_id' => NewsCategory::factory(),
            'news_author_id' => NewsAuthor::factory(),
            'slug' => Str::slug($title),
            'title' => ['en' => rtrim($title, '.'), 'km' => null],
            'excerpt' => ['en' => fake()->sentence(14), 'km' => null],
            'body' => ['en' => fake()->paragraphs(3, true), 'km' => null],
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHour(),
            'is_featured' => false,
            'allow_comments' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ArticleStatus::Draft, 'published_at' => null]);
    }
}
