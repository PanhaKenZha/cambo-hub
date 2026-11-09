<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Models\Role;
use App\Models\User;

/**
 * Authorization for the Roles page. Administrators can never change the
 * Super Admin role or a role they hold themselves.
 */
class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(PermissionArea::Roles->permission(PermissionAction::View));
    }

    public function create(User $actor): bool
    {
        return $actor->can(PermissionArea::Roles->permission(PermissionAction::Create));
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->can(PermissionArea::Roles->permission(PermissionAction::Edit))
            && ! $role->isLocked()
            && ! $actor->hasRole($role->name);
    }

    /**
     * Only custom roles that nobody holds can be deleted.
     */
    public function delete(User $actor, Role $role): bool
    {
        return $actor->can(PermissionArea::Roles->permission(PermissionAction::Delete))
            && ! $role->isSystem()
            && ! $actor->hasRole($role->name)
            && $role->users()->doesntExist();
    }
}
