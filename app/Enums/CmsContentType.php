<?php

namespace App\Enums;

/**
 * Kinds of content items listed on the homepage and in global search.
 * Promotional banners are not items: they are managed in Website › Banners.
 */
enum CmsContentType: string
{
    case ServiceCategory = 'service-category';
    case Story = 'story';
    case News = 'news';
    case Job = 'job';
    case Product = 'product';
    case Advertisement = 'advertisement';
    case Business = 'business';
    case Property = 'property';
    case SocialPost = 'social-post';
    case Event = 'event';
    case Course = 'course';

    public function label(): string
    {
        return match ($this) {
            self::ServiceCategory => 'Services',
            self::Story => 'Stories',
            self::News => 'News',
            self::Job => 'Jobs',
            self::Product => 'Products',
            self::Advertisement => 'Sponsored',
            self::Business => 'Businesses',
            self::Property => 'Properties',
            self::SocialPost => 'Community',
            self::Event => 'Events',
            self::Course => 'Courses',
        };
    }

    /**
     * Icon name from public/images/icons used when an item has no image.
     */
    public function icon(): string
    {
        return match ($this) {
            self::ServiceCategory => 'menu',
            self::Story => 'stories',
            self::Advertisement => 'advertising',
            self::News => 'news',
            self::Job => 'jobs',
            self::Product => 'shopping',
            self::Business => 'business',
            self::Property => 'property',
            self::SocialPost => 'social',
            self::Event => 'events',
            self::Course => 'education',
        };
    }

    /**
     * The platform module this content belongs to. Its items disappear while the module is off.
     */
    public function module(): ?PlatformModule
    {
        return match ($this) {
            self::News => PlatformModule::News,
            self::Job => PlatformModule::Jobs,
            self::Product => PlatformModule::Marketplace,
            self::Business => PlatformModule::Business,
            self::Property => PlatformModule::Listings,
            self::Story, self::SocialPost => PlatformModule::Social,
            self::Event => PlatformModule::Events,
            self::Course => PlatformModule::Education,
            self::ServiceCategory, self::Advertisement => null,
        };
    }

    public function isAvailable(): bool
    {
        return $this->module()?->isEnabled() ?? true;
    }

    /**
     * Whether visitors can find this type through global search.
     */
    public function isSearchable(): bool
    {
        return ! in_array($this, [self::Advertisement, self::Story, self::ServiceCategory], true);
    }

    /**
     * Searchable types whose module is switched on.
     *
     * @return list<self>
     */
    public static function searchable(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type->isSearchable() && $type->isAvailable()));
    }
}
