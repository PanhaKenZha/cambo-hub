<?php

namespace App\Enums;

enum BannerPlacement: string
{
    case HomepageHero = 'homepage_hero';
    case TopStrip = 'top_strip';
    case Sidebar = 'sidebar';

    public function label(): string
    {
        return match ($this) {
            self::HomepageHero => 'Homepage hero slider',
            self::TopStrip => 'Top strip (all pages)',
            self::Sidebar => 'Sidebar',
        };
    }

    /**
     * Recommended image size, shown to administrators when uploading.
     */
    public function recommendedSize(): string
    {
        return match ($this) {
            self::HomepageHero => '1920 × 640 px',
            self::TopStrip => '1920 × 120 px',
            self::Sidebar => '600 × 800 px',
        };
    }
}
