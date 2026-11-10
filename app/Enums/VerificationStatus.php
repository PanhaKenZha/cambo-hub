<?php

namespace App\Enums;

/**
 * Whether Cambo Hub has checked that a company is real.
 */
enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Not verified',
            self::Pending => 'Waiting for review',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unverified => 'gray',
            self::Pending => 'amber',
            self::Verified => 'green',
            self::Rejected => 'red',
        };
    }
}
