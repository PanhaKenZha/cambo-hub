<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Enums\CmsContentType;
use App\Models\CmsContent;
use App\Models\NewsArticle;

/**
 * Keeps the homepage / search item (CmsContent) for each news article in step with the article.
 *
 * Published articles get one item; drafts, articles in review and rejected articles get none.
 * Scheduled articles get an item whose start date is the publish date, so they appear on time.
 */
class NewsContentSync
{
    public const string SOURCE_TYPE = 'news_article';

    public function sync(NewsArticle $article): void
    {
        if ($article->status !== ArticleStatus::Published) {
            $this->remove($article);

            return;
        }

        $article->loadMissing(['category', 'author']);
        $publishedAt = $article->published_at ?? $article->created_at ?? now();

        CmsContent::updateOrCreate(
            ['source_type' => self::SOURCE_TYPE, 'source_id' => $article->id],
            [
                'content_type' => CmsContentType::News,
                'title' => $article->translate('title', 'en'),
                'summary' => $article->translate('excerpt', 'en') ?: null,
                'badge' => $article->category?->translate('name', 'en'),
                'image_path' => $article->cover_image_path ? 'storage/'.$article->cover_image_path : null,
                'link_url' => '/news/'.$article->slug,
                'meta' => array_filter([
                    'module' => 'news',
                    'author' => $article->author?->name,
                ]),
                'is_featured' => $article->is_featured,
                'is_published' => true,
                'published_at' => $publishedAt,
                'starts_at' => $publishedAt,
                'sort_order' => 0,
            ],
        );

        app(SiteContent::class)->flush();
    }

    public function remove(NewsArticle $article): void
    {
        $deleted = CmsContent::where('source_type', self::SOURCE_TYPE)->where('source_id', $article->id)->delete();

        if ($deleted > 0) {
            app(SiteContent::class)->flush();
        }
    }
}
