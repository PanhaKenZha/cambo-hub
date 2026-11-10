<?php

namespace App\Models;

use App\Enums\AppealStatus;
use App\Enums\PlatformModule;
use Database\Factories\ModerationAppealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A user's request to reverse a rejected submission or a violation.
 */
#[Fillable(['appealable_type', 'appealable_id', 'user_id', 'message', 'status', 'decided_by', 'decided_at', 'decision_note'])]
class ModerationAppeal extends Model
{
    /** @use HasFactory<ModerationAppealFactory> */
    use HasFactory;

    /**
     * The rejected ApprovalRequest or UserViolation being appealed.
     *
     * @return MorphTo<Model, $this>
     */
    public function appealable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Appeals a moderator may see. Account violations are visible to every moderator;
     * appeals about rejected submissions only to moderators of that module, as in the Approval center.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $visibleModules = PlatformModule::visibleTo($user);

        $query->where(fn (Builder $query) => $query
            ->where('appealable_type', (new UserViolation)->getMorphClass())
            ->orWhereHasMorph('appealable', [ApprovalRequest::class], fn (Builder $query) => $query->whereIn('module', $visibleModules)));
    }

    public function isPending(): bool
    {
        return $this->status === AppealStatus::Pending;
    }

    /**
     * The moderator who made the decision being appealed.
     */
    public function originalDeciderId(): ?int
    {
        return match (true) {
            $this->appealable instanceof ApprovalRequest => $this->appealable->reviewed_by,
            $this->appealable instanceof UserViolation => $this->appealable->issued_by,
            default => null,
        };
    }

    public function subjectLabel(): string
    {
        return match (true) {
            $this->appealable instanceof ApprovalRequest => 'Rejected submission: '.$this->appealable->title,
            $this->appealable instanceof UserViolation => $this->appealable->type->label().': '.$this->appealable->reason,
            default => 'Removed item',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AppealStatus::class,
            'decided_at' => 'datetime',
        ];
    }
}
