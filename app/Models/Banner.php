<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use App\Models\Concerns\FlushesSiteContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * An image banner shown in one placement during an optional date range.
 */
#[Fillable(['placement', 'title', 'subtitle', 'button_label', 'link_url', 'image_path', 'is_active', 'starts_at', 'ends_at', 'sort_order'])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use FlushesSiteContentCache, HasFactory, HasTranslations;

    public function isLive(): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Uploaded images live on the public disk; seeded defaults point at bundled files in public/images.
     */
    public function imageUrl(): ?string
    {
        return match (true) {
            blank($this->image_path) => null,
            str_starts_with($this->image_path, 'images/') => asset($this->image_path),
            default => Storage::disk('public')->url($this->image_path),
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
            'placement' => BannerPlacement::class,
            'title' => 'array',
            'subtitle' => 'array',
            'button_label' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
