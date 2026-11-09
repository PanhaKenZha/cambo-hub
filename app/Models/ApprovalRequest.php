<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\PlatformModule;
use Database\Factories\ApprovalRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A piece of content waiting for, or decided by, a moderator.
 */
#[Fillable(['approvable_type', 'approvable_id', 'module', 'title', 'summary', 'status', 'submitted_by', 'assigned_to', 'reviewed_by', 'reviewed_at', 'rejection_reason'])]
class ApprovalRequest extends Model
{
    /** @use HasFactory<ApprovalRequestFactory> */
    use HasFactory;

    /**
     * @return MorphTo<Model, $this>
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
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
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Review history, oldest first.
     *
     * @return HasMany<ApprovalEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(ApprovalEvent::class)->oldest('id');
    }

    /**
     * @return MorphMany<ModerationAppeal, $this>
     */
    public function appeals(): MorphMany
    {
        return $this->morphMany(ModerationAppeal::class, 'appealable');
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::Pending;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', ApprovalStatus::Pending);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'module' => PlatformModule::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
