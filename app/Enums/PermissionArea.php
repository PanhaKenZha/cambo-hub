<?php

namespace App\Enums;

/**
 * Areas of the admin panel. Each area supports a set of actions, and every
 * area/action pair is one permission named "{area}.{action}", e.g. "news.edit".
 */
enum PermissionArea: string
{
    case Dashboard = 'dashboard';
    case Administrators = 'admins';
    case Roles = 'roles';
    case AuditLogs = 'audit-logs';
    case Settings = 'settings';
    case WebsiteContent = 'cms';
    case Approvals = 'approvals';
    case News = 'news';
    case Jobs = 'jobs';
    case Marketplace = 'marketplace';
    case Social = 'social';
    case Business = 'business';
    case Listings = 'listings';
    case Hosting = 'hosting';
    case Education = 'education';
    case Events = 'events';
    case Finance = 'finance';
    case Support = 'support';

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Administrators => 'Administrators',
            self::Roles => 'Roles',
            self::AuditLogs => 'Audit logs',
            self::Settings => 'Platform settings',
            self::WebsiteContent => 'Website content',
            self::Approvals => 'Approvals & moderation',
            self::News => 'News',
            self::Jobs => 'Jobs',
            self::Marketplace => 'Marketplace',
            self::Social => 'Social',
            self::Business => 'Businesses',
            self::Listings => 'Real estate & vehicles',
            self::Hosting => 'Hosting',
            self::Education => 'Education',
            self::Events => 'Events & bookings',
            self::Finance => 'Payments & finance',
            self::Support => 'Support tickets',
        };
    }

    /**
     * Section the area is listed under.
     */
    public function group(): string
    {
        return match ($this) {
            self::Dashboard, self::Administrators, self::Roles, self::AuditLogs, self::Settings, self::WebsiteContent, self::Approvals => 'Platform',
            self::Finance, self::Support => 'Finance & support',
            default => 'Modules',
        };
    }

    /**
     * @return list<PermissionAction>
     */
    public function actions(): array
    {
        return match ($this) {
            self::Dashboard, self::AuditLogs => [PermissionAction::View],
            self::Settings => [PermissionAction::View, PermissionAction::Edit],
            self::Approvals => [PermissionAction::View, PermissionAction::Manage],
            self::Administrators => [PermissionAction::View, PermissionAction::Create, PermissionAction::Edit, PermissionAction::Manage],
            self::Roles => [PermissionAction::View, PermissionAction::Create, PermissionAction::Edit, PermissionAction::Delete],
            default => PermissionAction::cases(),
        };
    }

    public function supports(PermissionAction $action): bool
    {
        return in_array($action, $this->actions(), true);
    }

    /**
     * Permission name for one action in this area, e.g. "news.edit".
     */
    public function permission(PermissionAction $action): string
    {
        return "{$this->value}.{$action->value}";
    }

    /**
     * Every permission name in this area.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return array_map(fn (PermissionAction $action): string => $this->permission($action), $this->actions());
    }

    /**
     * Every permission name across all areas.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        return array_merge(...array_map(fn (self $area): array => $area->permissions(), self::cases()));
    }
}
