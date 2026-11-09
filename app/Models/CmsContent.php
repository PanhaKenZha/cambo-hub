<?php

namespace App\Models;

use App\Enums\CmsContentType;
use Database\Factories\CmsContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One piece of CMS published content shown on the homepage and in search,
 * e.g. a news article, job, product, property, story, banner, or advert.
 */
#[Fillable(['content_type', 'title', 'summary', 'image_path', 'link_url', 'badge', 'meta', 'is_featured', 'is_published', 'published_at', 'starts_at', 'ends_at', 'sort_order'])]
class CmsContent extends Model
{
    /** @use HasFactory<CmsContentFactory> */
    use HasFactory;

    /**
     * Read a type specific detail such as "company", "price", or "location".
     */
    public function detail(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, $key, $default);
    }

    public function linkUrl(): ?string
    {
        return filled($this->link_url) ? url($this->link_url) : null;
    }

    public function imageUrl(?string $fallback = null): ?string
    {
        $path = $this->image_path ?: $fallback;

        if (blank($path)) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : asset($path);
    }

    /**
     * Restrict to published content whose schedule window includes now.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $now = now();

        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /**
     * Featured items first, then CMS sort order, then most recent.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function curated(Builder $query): void
    {
        $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_type' => CmsContentType::class,
            'meta' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
