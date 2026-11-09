<?php

namespace App\Models\Concerns;

use App\Services\SiteContent;

/**
 * Refreshes the public website's cached content whenever this model changes.
 */
trait FlushesSiteContentCache
{
    public static function bootFlushesSiteContentCache(): void
    {
        static::saved(fn () => app(SiteContent::class)->flush());
        static::deleted(fn () => app(SiteContent::class)->flush());
    }
}
