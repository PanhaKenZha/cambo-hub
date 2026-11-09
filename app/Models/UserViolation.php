<?php

namespace App\Models;

use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use Database\Factories\UserViolationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A warning, strike, suspension or ban given to a user by a moderator.
 */
#[Fillable(['user_id', 'issued_by', 'content_report_id', 'type', 'reason', 'status', 'expires_at', 'overturned_by', 'overturned_at'])]
class UserViolation extends Model
{
    /** @use HasFactory<UserViolationFactory> */
    use HasFactory;

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
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * @return BelongsTo<ContentReport, $this>
     */
    public function contentReport(): BelongsTo
    {
        return $this->belongsTo(ContentReport::class);
    }

    /**
     * @return MorphMany<ModerationAppeal, $this>
     */
    public function appeals(): MorphMany
    {
        return $this->morphMany(ModerationAppeal::class, 'appealable');
    }

    public function isActive(): bool
    {
        return $this->status === ViolationStatus::Active;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ViolationType::class,
            'status' => ViolationStatus::class,
            'expires_at' => 'datetime',
            'overturned_at' => 'datetime',
        ];
    }
}
