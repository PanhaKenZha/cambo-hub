<?php

namespace App\Enums;

/**
 * Kinds of content the central CMS publishes. Each type has a matching
 * homepage section that renders its items with a dedicated partial.
 */
enum CmsContentType: string
{
    case ServiceCategory = 'service-category';
    case Story = 'story';
    case PromotionalBanner = 'promotional-banner';
    case News = 'news';
    case Job = 'job';
    case Product = 'product';
    case Advertisement = 'advertisement';
    case Business = 'business';
    case Property = 'property';
    case SocialPost = 'social-post';
    case Event = 'event';

    public function label(): string
    {
        return match ($this) {
            self::ServiceCategory => 'Services',
            self::Story => 'Stories',
            self::PromotionalBanner => 'Promotions',
            self::News => 'News',
            self::Job => 'Jobs',
            self::Product => 'Products',
            self::Advertisement => 'Sponsored',
            self::Business => 'Businesses',
            self::Property => 'Properties',
            self::SocialPost => 'Community',
            self::Event => 'Events',
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
            self::PromotionalBanner, self::Advertisement => 'advertising',
            self::News => 'news',
            self::Job => 'jobs',
            self::Product => 'shopping',
            self::Business => 'business',
            self::Property => 'property',
            self::SocialPost => 'social',
            self::Event => 'events',
        };
    }

    /**
     * Whether visitors can find this type through global search.
     */
    public function isSearchable(): bool
    {
        return ! in_array($this, [self::PromotionalBanner, self::Advertisement, self::Story], true);
    }

    /**
     * @return list<self>
     */
    public static function searchable(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type->isSearchable()));
    }
}
