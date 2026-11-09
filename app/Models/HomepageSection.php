<?php

namespace App\Models;

use App\Enums\HomepageSectionType;
use App\Models\Concerns\FlushesSiteContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\HomepageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * One block on the public homepage, in the order administrators choose.
 */
#[Fillable(['type', 'title', 'subtitle', 'button_label', 'link_url', 'image_path', 'item_count', 'is_visible', 'sort_order'])]
class HomepageSection extends Model
{
    /** @use HasFactory<HomepageSectionFactory> */
    use FlushesSiteContentCache, HasFactory, HasTranslations;

    /**
     * Visible to the public: switched on, and its module (if any) is enabled.
     */
    public function isShown(): bool
    {
        return $this->is_visible && ($this->type->module()?->isEnabled() ?? true);
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
            'type' => HomepageSectionType::class,
            'title' => 'array',
            'subtitle' => 'array',
            'button_label' => 'array',
            'is_visible' => 'boolean',
        ];
    }
}
