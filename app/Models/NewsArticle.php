<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Contracts\Reportable;
use App\Enums\ArticleStatus;
use App\Enums\PlatformModule;
use App\Models\Concerns\HasTranslations;
use App\Observers\NewsArticleObserver;
use Database\Factories\NewsArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * A news article. Text is stored in English and Khmer; the body is Markdown.
 */
#[ObservedBy(NewsArticleObserver::class)]
#[Fillable(['news_category_id', 'news_author_id', 'slug', 'title', 'excerpt', 'body', 'cover_image_path', 'meta_title', 'meta_description', 'status', 'published_at', 'is_featured', 'allow_comments', 'submitted_by', 'created_by', 'updated_by'])]
class NewsArticle extends Model implements Approvable, Reportable
{
    /** @use HasFactory<NewsArticleFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return BelongsTo<NewsCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    /**
     * @return BelongsTo<NewsAuthor, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(NewsAuthor::class, 'news_author_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return HasMany<NewsComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }

    /**
     * @return MorphMany<ApprovalRequest, $this>
     */
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    /**
     * Articles readers can see right now.
     *
     * @param  Builder<self>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isLive(): bool
    {
        return $this->status === ArticleStatus::Published && ($this->published_at === null || $this->published_at->isPast());
    }

    public function isScheduled(): bool
    {
        return $this->status === ArticleStatus::Published && $this->published_at?->isFuture();
    }

    /**
     * Status for display: Draft, In review, Scheduled, Published or Rejected.
     */
    public function stateLabel(): string
    {
        return $this->isScheduled() ? 'Scheduled' : $this->status->label();
    }

    public function stateColor(): string
    {
        return $this->isScheduled() ? 'violet' : $this->status->color();
    }

    /**
     * Body rendered from Markdown. Raw HTML and unsafe links are removed.
     */
    public function bodyHtml(?string $locale = null): string
    {
        return Str::markdown($this->translate('body', $locale), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image_path ? asset('storage/'.$this->cover_image_path) : null;
    }

    public function publicUrl(): string
    {
        return route('news.show', $this->slug);
    }

    public function approvalTitle(): string
    {
        return $this->translate('title', 'en');
    }

    public function approvalModule(): PlatformModule
    {
        return PlatformModule::News;
    }

    public function approvalPreviewUrl(): ?string
    {
        return route('admin.news.articles.preview', $this);
    }

    /**
     * Approved in the Approval center: publish now, or on the scheduled date.
     */
    public function markApproved(): void
    {
        $this->update(['status' => ArticleStatus::Published, 'published_at' => $this->published_at ?? now()]);
    }

    public function markRejected(string $reason): void
    {
        $this->update(['status' => ArticleStatus::Rejected]);
    }

    public function moderationTitle(): string
    {
        return $this->translate('title', 'en');
    }

    public function moderationOwner(): ?User
    {
        return $this->submitter ?? $this->author?->user;
    }

    public function moderationModule(): ?PlatformModule
    {
        return PlatformModule::News;
    }

    /**
     * Taken down after a report is upheld: back to draft, off the website.
     */
    public function takeDown(): void
    {
        $this->update(['status' => ArticleStatus::Draft, 'is_featured' => false]);
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
            'excerpt' => 'array',
            'body' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'views_count' => 'integer',
        ];
    }
}
