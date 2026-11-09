<?php

namespace App\Enums;

use App\Models\User;
use App\Services\PlatformSettings;

/**
 * Public platform services that administrators can switch on or off.
 */
enum PlatformModule: string
{
    case News = 'news';
    case Jobs = 'jobs';
    case Marketplace = 'marketplace';
    case Social = 'social';
    case Business = 'business';
    case Listings = 'listings';
    case Hosting = 'hosting';
    case Education = 'education';
    case Events = 'events';

    public function label(): string
    {
        return match ($this) {
            self::News => 'News',
            self::Jobs => 'Jobs',
            self::Marketplace => 'Marketplace',
            self::Social => 'Social',
            self::Business => 'Business directory',
            self::Listings => 'Real estate & vehicles',
            self::Hosting => 'Hosting',
            self::Education => 'Education',
            self::Events => 'Events & bookings',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::News => 'Articles, categories, authors and comments.',
            self::Jobs => 'Job listings, employers and applications.',
            self::Marketplace => 'Stores, sellers, products and orders.',
            self::Social => 'Posts, stories and community groups.',
            self::Business => 'Company profiles and verification.',
            self::Listings => 'Property and vehicle advertisements.',
            self::Hosting => 'Hosting packages, domains and subscriptions.',
            self::Education => 'Courses, instructors and enrolments.',
            self::Events => 'Events, organizers and bookings.',
        };
    }

    /**
     * Setting key that stores whether the module is on, e.g. "modules.news".
     */
    public function settingKey(): string
    {
        return "modules.{$this->value}";
    }

    public function isEnabled(): bool
    {
        return app(PlatformSettings::class)->moduleEnabled($this);
    }

    /**
     * Modules whose admin content this administrator may see.
     *
     * @return list<self>
     */
    public static function visibleTo(User $user): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $module): bool => $user->can(PermissionArea::from($module->value)->permission(PermissionAction::View)),
        ));
    }
}
