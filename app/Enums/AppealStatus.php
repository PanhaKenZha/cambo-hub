<?php

namespace App\Enums;

enum AppealStatus: string
{
    case Pending = 'pending';
    case Upheld = 'upheld';
    case Overturned = 'overturned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Upheld => 'Decision upheld',
            self::Overturned => 'Overturned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Upheld => 'gray',
            self::Overturned => 'green',
        };
    }
}
