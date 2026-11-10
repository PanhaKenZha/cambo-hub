<?php

namespace App\Enums;

/**
 * Progress of a job application through hiring.
 */
enum ApplicationStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Reviewed => 'gray',
            self::Shortlisted => 'violet',
            self::Rejected => 'red',
            self::Hired => 'green',
        };
    }
}
