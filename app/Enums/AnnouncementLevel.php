<?php

namespace App\Enums;

enum AnnouncementLevel: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Badge colour used in the admin panel.
     */
    public function color(): string
    {
        return match ($this) {
            self::Info => 'blue',
            self::Success => 'green',
            self::Warning => 'amber',
        };
    }
}
