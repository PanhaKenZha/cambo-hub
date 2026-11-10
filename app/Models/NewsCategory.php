<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NewsCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A news section such as City, Economy or Sports.
 */
#[Fillable(['slug', 'name', 'description', 'is_active', 'sort_order'])]
class NewsCategory extends Model
{
    /** @use HasFactory<NewsCategoryFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return HasMany<NewsArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
