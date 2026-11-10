<?php

namespace App\Observers;

use App\Models\NewsArticle;
use App\Services\NewsContentSync;

/**
 * Mirrors each news article onto the homepage and search whenever it changes.
 */
class NewsArticleObserver
{
    public function __construct(private NewsContentSync $sync) {}

    public function saved(NewsArticle $newsArticle): void
    {
        $this->sync->sync($newsArticle);
    }

    public function deleted(NewsArticle $newsArticle): void
    {
        $this->sync->remove($newsArticle);
    }
}
