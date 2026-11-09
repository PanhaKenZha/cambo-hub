<?php

namespace App\Enums;

/**
 * Building blocks of the public homepage. Developer 2 renders each type;
 * administrators choose the order, visibility and text.
 */
enum HomepageSectionType: string
{
    case Hero = 'hero';
    case FeaturedNews = 'featured_news';
    case LatestJobs = 'latest_jobs';
    case MarketplaceHighlights = 'marketplace_highlights';
    case FeaturedBusinesses = 'featured_businesses';
    case LatestListings = 'latest_listings';
    case UpcomingEvents = 'upcoming_events';
    case PopularCourses = 'popular_courses';
    case PromoBlock = 'promo_block';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero banner',
            self::FeaturedNews => 'Featured news',
            self::LatestJobs => 'Latest jobs',
            self::MarketplaceHighlights => 'Marketplace highlights',
            self::FeaturedBusinesses => 'Featured businesses',
            self::LatestListings => 'Latest property & vehicles',
            self::UpcomingEvents => 'Upcoming events',
            self::PopularCourses => 'Popular courses',
            self::PromoBlock => 'Promotional block',
        };
    }

    /**
     * The module the section depends on. It is hidden while that module is turned off.
     */
    public function module(): ?PlatformModule
    {
        return match ($this) {
            self::FeaturedNews => PlatformModule::News,
            self::LatestJobs => PlatformModule::Jobs,
            self::MarketplaceHighlights => PlatformModule::Marketplace,
            self::FeaturedBusinesses => PlatformModule::Business,
            self::LatestListings => PlatformModule::Listings,
            self::UpcomingEvents => PlatformModule::Events,
            self::PopularCourses => PlatformModule::Education,
            self::Hero, self::PromoBlock => null,
        };
    }

    /**
     * Whether the section shows a list of items, so the number of items is configurable.
     */
    public function showsItems(): bool
    {
        return $this->module() !== null;
    }

    /**
     * Promotional blocks have their own image and call-to-action button.
     */
    public function hasCallToAction(): bool
    {
        return $this === self::PromoBlock;
    }
}
