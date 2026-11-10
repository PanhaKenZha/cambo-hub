<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsComment>
 */
class NewsCommentFactory extends Factory
{
    /**
     * Define the model's default state: a comment waiting for moderation.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_article_id' => NewsArticle::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(12),
            'status' => CommentStatus::Pending,
        ];
    }
}
