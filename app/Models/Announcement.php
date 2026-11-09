<?php

namespace App\Models;

use App\Enums\AnnouncementLevel;
use App\Models\Concerns\FlushesSiteContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A site-wide notice shown at the top of the public website.
 */
#[Fillable(['message', 'link_url', 'link_label', 'level', 'is_active', 'is_dismissible', 'starts_at', 'ends_at', 'created_by'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use FlushesSiteContentCache, HasFactory, HasTranslations;

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLive(): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Live, Scheduled, Ended or Off.
     */
    public function stateLabel(): string
    {
        return match (true) {
            ! $this->is_active => 'Off',
            $this->starts_at?->isFuture() => 'Scheduled',
            $this->ends_at !== null && $this->ends_at->isPast() => 'Ended',
            default => 'Live',
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
            'message' => 'array',
            'link_label' => 'array',
            'level' => AnnouncementLevel::class,
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
