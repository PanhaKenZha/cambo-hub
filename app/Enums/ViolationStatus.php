<?php

namespace App\Enums;

enum ViolationStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Overturned = 'overturned';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'red',
            self::Expired => 'gray',
            self::Overturned => 'green',
        };
    }
}
