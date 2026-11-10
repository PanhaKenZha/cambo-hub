<?php

namespace App\Enums;

/**
 * How candidates apply for a job.
 */
enum ApplyMethod: string
{
    case OnSite = 'on_site';
    case ExternalUrl = 'external_url';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::OnSite => 'On Cambo Hub (CV upload)',
            self::ExternalUrl => 'On the employer’s website',
            self::Email => 'By email',
        };
    }
}
