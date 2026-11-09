<?php

namespace App\Enums;

/**
 * Why someone reported a piece of content.
 */
enum ReportReason: string
{
    case Spam = 'spam';
    case Scam = 'scam';
    case Harassment = 'harassment';
    case HateSpeech = 'hate_speech';
    case Violence = 'violence';
    case AdultContent = 'adult_content';
    case Misinformation = 'misinformation';
    case Copyright = 'copyright';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam',
            self::Scam => 'Scam or fraud',
            self::Harassment => 'Harassment or bullying',
            self::HateSpeech => 'Hate speech',
            self::Violence => 'Violence or threats',
            self::AdultContent => 'Adult content',
            self::Misinformation => 'False information',
            self::Copyright => 'Copyright infringement',
            self::Other => 'Other',
        };
    }
}
