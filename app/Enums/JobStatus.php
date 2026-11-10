<?php

namespace App\Enums;

/**
 * Where a job listing is in its life cycle. Published jobs past their closing date are "Closed".
 */
enum JobStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InReview => 'In review',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft, self::Closed => 'gray',
            self::InReview => 'amber',
            self::Published => 'green',
            self::Rejected => 'red',
        };
    }
}
