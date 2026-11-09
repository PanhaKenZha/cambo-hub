<?php

namespace App\Enums;

/**
 * Building blocks of the public homepage. Each type has a matching partial in
 * resources/views/home/sections; administrators choose the order, visibility and text.
 */
enum HomepageSectionType: string
{
    case Hero = 'hero';
    case ServiceCategories = 'service_categories';
    case Stories = 'stories';
    case PromotionalBanners = 'promotional_banners';
    case FeaturedNews = 'featured_news';
    case LatestJobs = 'latest_jobs';
    case MarketplaceHighlights = 'marketplace_highlights';
    case Advertisements = 'advertisements';
    case FeaturedBusinesses = 'featured_businesses';
    case LatestListings = 'latest_listings';
    case SocialHighlights = 'social_highlights';
    case UpcomingEvents = 'upcoming_events';
    case PopularCourses = 'popular_courses';
    case PromoBlock = 'promo_block';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero banner',
            self::ServiceCategories => 'Service categories',
            self::Stories => 'Stories',
            self::PromotionalBanners => 'Promotional banners',
            self::FeaturedNews => 'Featured news',
            self::LatestJobs => 'Latest jobs',
            self::MarketplaceHighlights => 'Marketplace highlights',
            self::Advertisements => 'Advertisements',
            self::FeaturedBusinesses => 'Featured businesses',
            self::LatestListings => 'Latest property & vehicles',
            self::SocialHighlights => 'Social highlights',
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
            self::Stories, self::SocialHighlights => PlatformModule::Social,
            self::Hero, self::ServiceCategories, self::PromotionalBanners, self::Advertisements, self::PromoBlock => null,
        };
    }

    /**
     * Kind of CMS content the section lists, or null when it has no item list.
     */
    public function contentType(): ?CmsContentType
    {
        return match ($this) {
            self::ServiceCategories => CmsContentType::ServiceCategory,
            self::Stories => CmsContentType::Story,
            self::FeaturedNews => CmsContentType::News,
            self::LatestJobs => CmsContentType::Job,
            self::MarketplaceHighlights => CmsContentType::Product,
            self::Advertisements => CmsContentType::Advertisement,
            self::FeaturedBusinesses => CmsContentType::Business,
            self::LatestListings => CmsContentType::Property,
            self::SocialHighlights => CmsContentType::SocialPost,
            self::UpcomingEvents => CmsContentType::Event,
            self::PopularCourses => CmsContentType::Course,
            self::Hero, self::PromotionalBanners, self::PromoBlock => null,
        };
    }

    /**
     * Whether the section shows a list of items, so the number of items is configurable.
     * Promotional banners list the banners from Website › Banners (homepage placement).
     */
    public function showsItems(): bool
    {
        return $this->contentType() !== null || $this === self::PromotionalBanners;
    }

    /**
     * Promotional blocks have their own image and call-to-action button.
     */
    public function hasCallToAction(): bool
    {
        return $this === self::PromoBlock;
    }

    /**
     * Item lists can link to a "view all" page with a custom label.
     */
    public function hasLink(): bool
    {
        return $this->contentType() !== null && $this !== self::Advertisements && $this !== self::Stories;
    }
}
