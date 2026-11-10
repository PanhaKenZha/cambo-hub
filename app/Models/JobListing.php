<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Contracts\Reportable;
use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Enums\PlatformModule;
use App\Models\Concerns\HasTranslations;
use App\Observers\JobListingObserver;
use Database\Factories\JobListingFactory;
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
 * A job vacancy posted by a company. Text is stored in English and Khmer; the description is Markdown.
 */
#[ObservedBy(JobListingObserver::class)]
#[Fillable(['company_id', 'job_category_id', 'slug', 'title', 'description', 'employment_type', 'location', 'is_remote', 'salary_min', 'salary_max', 'salary_currency', 'salary_period', 'apply_method', 'apply_url', 'apply_email', 'status', 'published_at', 'closes_at', 'is_featured', 'featured_until', 'submitted_by', 'created_by', 'updated_by'])]
class JobListing extends Model implements Approvable, Reportable
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<JobCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * @return MorphMany<ApprovalRequest, $this>
     */
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    /**
     * Jobs candidates can see and apply for right now.
     *
     * @param  Builder<self>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', JobStatus::Published)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now()));
    }

    public function isLive(): bool
    {
        return $this->status === JobStatus::Published
            && ($this->published_at === null || $this->published_at->isPast())
            && ! $this->hasPassedClosingDate();
    }

    public function hasPassedClosingDate(): bool
    {
        return $this->closes_at !== null && $this->closes_at->isPast();
    }

    /**
     * Featured, and the featuring period (if any) has not ended.
     */
    public function isFeaturedNow(): bool
    {
        return $this->is_featured && ($this->featured_until === null || $this->featured_until->isFuture());
    }

    /**
     * Status for display: Draft, In review, Scheduled, Published, Closed or Rejected.
     */
    public function stateLabel(): string
    {
        return match (true) {
            $this->status === JobStatus::Published && $this->hasPassedClosingDate() => 'Closed',
            $this->status === JobStatus::Published && $this->published_at?->isFuture() => 'Scheduled',
            default => $this->status->label(),
        };
    }

    public function stateColor(): string
    {
        return match ($this->stateLabel()) {
            'Closed' => 'gray',
            'Scheduled' => 'violet',
            default => $this->status->color(),
        };
    }

    /**
     * e.g. "$900 – $1,300 / month", "From 4,000,000 KHR / month" or "Negotiable".
     */
    public function salaryLabel(): string
    {
        if ($this->salary_min === null && $this->salary_max === null) {
            return 'Negotiable';
        }

        $format = fn (int $amount): string => $this->salary_currency === 'USD' ? '$'.number_format($amount) : number_format($amount).' KHR';
        $range = match (true) {
            $this->salary_min !== null && $this->salary_max !== null && $this->salary_min !== $this->salary_max => $format($this->salary_min).' – '.$format($this->salary_max),
            $this->salary_min !== null && $this->salary_max === null => 'From '.$format($this->salary_min),
            $this->salary_min === null => 'Up to '.$format($this->salary_max),
            default => $format($this->salary_min),
        };

        return "{$range} / {$this->salary_period}";
    }

    public function locationLabel(): string
    {
        return collect([$this->location, $this->is_remote ? 'Remote' : null])->filter()->implode(' · ') ?: 'Cambodia';
    }

    /**
     * Description rendered from Markdown. Raw HTML and unsafe links are removed.
     */
    public function descriptionHtml(?string $locale = null): string
    {
        return Str::markdown($this->translate('description', $locale), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function publicUrl(): string
    {
        return route('jobs.show', $this->slug);
    }

    public function approvalTitle(): string
    {
        return $this->translate('title', 'en').' — '.$this->company?->name;
    }

    public function approvalModule(): PlatformModule
    {
        return PlatformModule::Jobs;
    }

    public function approvalPreviewUrl(): ?string
    {
        return route('admin.jobs.listings.edit', $this);
    }

    /**
     * Approved in the Approval center: publish now, or on the scheduled date.
     */
    public function markApproved(): void
    {
        $this->update(['status' => JobStatus::Published, 'published_at' => $this->published_at ?? now()]);
    }

    public function markRejected(string $reason): void
    {
        $this->update(['status' => JobStatus::Rejected]);
    }

    public function moderationTitle(): string
    {
        return $this->approvalTitle();
    }

    public function moderationOwner(): ?User
    {
        return $this->submitter ?? $this->company?->owner;
    }

    public function moderationModule(): ?PlatformModule
    {
        return PlatformModule::Jobs;
    }

    /**
     * Taken down after a report is upheld: back to draft, off the website.
     */
    public function takeDown(): void
    {
        $this->update(['status' => JobStatus::Draft, 'is_featured' => false]);
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
            'description' => 'array',
            'employment_type' => EmploymentType::class,
            'apply_method' => ApplyMethod::class,
            'status' => JobStatus::class,
            'is_remote' => 'boolean',
            'is_featured' => 'boolean',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'published_at' => 'datetime',
            'closes_at' => 'datetime',
            'featured_until' => 'datetime',
            'views_count' => 'integer',
        ];
    }
}
