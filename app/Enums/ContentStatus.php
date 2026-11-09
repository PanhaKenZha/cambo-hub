<?php

namespace App\Enums;

/**
 * Publishing state of website content. A published item with a future
 * publish date is shown as "Scheduled".
 */
enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
