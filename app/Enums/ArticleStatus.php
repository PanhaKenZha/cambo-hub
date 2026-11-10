<?php

namespace App\Enums;

/**
 * Where a news article is in its life cycle. A published article with a
 * future publish date is shown as "Scheduled".
 */
enum ArticleStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InReview => 'In review',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InReview => 'amber',
            self::Published => 'green',
            self::Rejected => 'red',
        };
    }
}
