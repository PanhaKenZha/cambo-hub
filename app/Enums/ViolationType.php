<?php

namespace App\Enums;

/**
 * Penalties a moderator can give a user, from least to most severe.
 */
enum ViolationType: string
{
    case Warning = 'warning';
    case Strike = 'strike';
    case Suspension = 'suspension';
    case Ban = 'ban';

    public function label(): string
    {
        return match ($this) {
            self::Warning => 'Warning',
            self::Strike => 'Strike',
            self::Suspension => 'Temporary suspension',
            self::Ban => 'Permanent ban',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Warning => 'amber',
            self::Strike => 'violet',
            self::Suspension, self::Ban => 'red',
        };
    }

    /**
     * Whether this penalty blocks the user from signing in.
     */
    public function blocksAccess(): bool
    {
        return in_array($this, [self::Suspension, self::Ban], true);
    }
}
