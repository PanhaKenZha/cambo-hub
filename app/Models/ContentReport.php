<?php

namespace App\Models;

use App\Enums\PlatformModule;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use Database\Factories\ContentReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A user's report that a piece of content breaks the platform rules.
 */
#[Fillable(['reportable_type', 'reportable_id', 'module', 'title', 'reporter_id', 'reason', 'details', 'status', 'handled_by', 'handled_at', 'resolution_note', 'content_taken_down'])]
class ContentReport extends Model
{
    /** @use HasFactory<ContentReportFactory> */
    use HasFactory;

    /**
     * @return MorphTo<Model, $this>
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * @return HasOne<UserViolation, $this>
     */
    public function violation(): HasOne
    {
        return $this->hasOne(UserViolation::class);
    }

    public function isOpen(): bool
    {
        return $this->status === ReportStatus::Open;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module' => PlatformModule::class,
            'reason' => ReportReason::class,
            'status' => ReportStatus::class,
            'handled_at' => 'datetime',
            'content_taken_down' => 'boolean',
        ];
    }
}
