<?php

namespace App\Enums;

/**
 * Administrator roles and the permissions each one is granted.
 */
enum AdminRole: string
{
    case SuperAdmin = 'super-admin';
    case PlatformAdmin = 'platform-admin';
    case NewsAdmin = 'news-admin';
    case JobsAdmin = 'jobs-admin';
    case MarketplaceAdmin = 'marketplace-admin';
    case SocialModerator = 'social-moderator';
    case BusinessAdmin = 'business-admin';
    case HostingAdmin = 'hosting-admin';
    case FinanceAdmin = 'finance-admin';
    case SupportAgent = 'support-agent';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::PlatformAdmin => 'Platform Admin',
            self::NewsAdmin => 'News Admin',
            self::JobsAdmin => 'Jobs Admin',
            self::MarketplaceAdmin => 'Marketplace Admin',
            self::SocialModerator => 'Social Moderator',
            self::BusinessAdmin => 'Business Admin',
            self::HostingAdmin => 'Hosting Admin',
            self::FinanceAdmin => 'Finance Admin',
            self::SupportAgent => 'Support Agent',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full control of the platform, including administrators and roles.',
            self::PlatformAdmin => 'Runs day-to-day platform operations across all modules.',
            self::NewsAdmin => 'Manages news articles, authors and comments.',
            self::JobsAdmin => 'Manages job listings, employers and company verification.',
            self::MarketplaceAdmin => 'Manages stores, sellers, products and orders.',
            self::SocialModerator => 'Moderates posts, stories, groups and reports.',
            self::BusinessAdmin => 'Verifies businesses and manages property and vehicle listings.',
            self::HostingAdmin => 'Manages hosting packages, subscriptions and provisioning.',
            self::FinanceAdmin => 'Manages payments, invoices, refunds and revenue reports.',
            self::SupportAgent => 'Handles support tickets from users.',
        };
    }

    /**
     * Default permissions, applied when the role is first created or reset.
     *
     * @return list<string>
     */
    public function defaultPermissions(): array
    {
        $areas = match ($this) {
            self::SuperAdmin => PermissionArea::cases(),
            self::PlatformAdmin => array_filter(
                PermissionArea::cases(),
                fn (PermissionArea $area): bool => ! in_array($area, [PermissionArea::Administrators, PermissionArea::Roles, PermissionArea::Finance], true),
            ),
            self::NewsAdmin => [PermissionArea::News, PermissionArea::Approvals],
            self::JobsAdmin => [PermissionArea::Jobs, PermissionArea::Approvals],
            self::MarketplaceAdmin => [PermissionArea::Marketplace, PermissionArea::Approvals],
            self::SocialModerator => [PermissionArea::Social, PermissionArea::Approvals],
            self::BusinessAdmin => [PermissionArea::Business, PermissionArea::Listings, PermissionArea::Approvals],
            self::HostingAdmin => [PermissionArea::Hosting],
            self::FinanceAdmin => [PermissionArea::Finance],
            self::SupportAgent => [PermissionArea::Support],
        };

        $permissions = array_merge(...array_map(fn (PermissionArea $area): array => $area->permissions(), array_values($areas)));

        return array_values(array_unique([PermissionArea::Dashboard->permission(PermissionAction::View), ...$permissions]));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
