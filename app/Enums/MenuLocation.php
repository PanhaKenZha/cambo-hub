<?php

namespace App\Enums;

enum MenuLocation: string
{
    case Header = 'header';
    case Footer = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header navigation',
            self::Footer => 'Footer links',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Header => 'Main navigation at the top of every page. Items can have one level of sub-items.',
            self::Footer => 'Link columns at the bottom of every page. Top-level items become column headings.',
        };
    }
}
