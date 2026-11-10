<?php

namespace App\Enums;

/**
 * Moderation state of a reader comment. New comments wait for approval.
 */
enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting',
            self::Approved => 'Approved',
            self::Hidden => 'Hidden',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'green',
            self::Hidden => 'gray',
        };
    }
}
