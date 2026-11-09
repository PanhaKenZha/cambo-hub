<?php

namespace App\Models;

use App\Enums\MenuLocation;
use App\Models\Concerns\FlushesSiteContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A link in the header or footer menu. Links either to a CMS page or to a URL.
 */
#[Fillable(['location', 'parent_id', 'label', 'page_id', 'url', 'opens_in_new_tab', 'is_visible', 'sort_order'])]
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use FlushesSiteContentCache, HasFactory, HasTranslations;

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function resolvedUrl(): ?string
    {
        return $this->page ? $this->page->publicUrl() : $this->url;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location' => MenuLocation::class,
            'label' => 'array',
            'opens_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }
}
