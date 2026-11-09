<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\FlushesSiteContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A custom website page (About, Terms, Privacy, ...). Body text is Markdown.
 */
#[Fillable(['slug', 'title', 'body', 'meta_title', 'meta_description', 'status', 'published_at', 'unpublish_at', 'created_by', 'updated_by'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use FlushesSiteContentCache, HasFactory, HasTranslations;

    /**
     * @return BelongsTo<User, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Pages visitors can see right now.
     *
     * @param  Builder<self>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('unpublish_at')->orWhere('unpublish_at', '>', now()));
    }

    public function isLive(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->published_at === null || $this->published_at->isPast())
            && ($this->unpublish_at === null || $this->unpublish_at->isFuture());
    }

    /**
     * Draft, Scheduled, Published or Expired.
     */
    public function stateLabel(): string
    {
        return match (true) {
            $this->status === ContentStatus::Draft => 'Draft',
            $this->published_at?->isFuture() => 'Scheduled',
            $this->unpublish_at !== null && $this->unpublish_at->isPast() => 'Expired',
            default => 'Published',
        };
    }

    public function stateColor(): string
    {
        return match ($this->stateLabel()) {
            'Published' => 'green',
            'Scheduled' => 'violet',
            'Expired' => 'gray',
            default => 'amber',
        };
    }

    /**
     * Body rendered from Markdown. Raw HTML is stripped, so editors cannot inject scripts.
     */
    public function bodyHtml(?string $locale = null): string
    {
        return Str::markdown($this->translate('body', $locale), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function publicUrl(): string
    {
        return route('pages.show', $this->slug);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'body' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }
}
