<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Database\Factories\NewsCommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reader comment on a news article. Shown only after a moderator approves it.
 */
#[Fillable(['news_article_id', 'user_id', 'body', 'status', 'ip_address', 'moderated_by', 'moderated_at'])]
class NewsComment extends Model
{
    /** @use HasFactory<NewsCommentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<NewsArticle, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'news_article_id');
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
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'moderated_at' => 'datetime',
        ];
    }
}
