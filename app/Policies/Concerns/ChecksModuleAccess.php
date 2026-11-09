<?php

namespace App\Policies\Concerns;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\PlatformModule;
use App\Models\User;

/**
 * Moderation work is scoped by module: reviewing news needs "news.*" permissions
 * as well as the "approvals.*" permissions.
 */
trait ChecksModuleAccess
{
    protected function canModerate(User $user, PermissionAction $action): bool
    {
        return $user->can(PermissionArea::Approvals->permission($action));
    }

    protected function canAccessModule(User $user, ?PlatformModule $module, PermissionAction $action): bool
    {
        return $module === null || $user->can(PermissionArea::from($module->value)->permission($action));
    }
}
