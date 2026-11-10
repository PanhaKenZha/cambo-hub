<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NewsAuthorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The byline shown on articles. Optionally linked to a user account.
 */
#[Fillable(['user_id', 'name', 'slug', 'bio', 'avatar_path', 'is_active'])]
class NewsAuthor extends Model
{
    /** @use HasFactory<NewsAuthorFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<NewsArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bio' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
