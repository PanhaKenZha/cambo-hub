<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Models\Role;
use App\Models\User;

/**
 * Authorization for managing administrator accounts.
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(PermissionArea::Administrators->permission(PermissionAction::View));
    }

    public function create(User $actor): bool
    {
        return $actor->can(PermissionArea::Administrators->permission(PermissionAction::Create));
    }

    public function view(User $actor, User $target): bool
    {
        return $this->viewAny($actor) && $target->isAdmin();
    }

    /**
     * Only a Super Admin may change another Super Admin.
     */
    public function update(User $actor, User $target): bool
    {
        return $actor->can(PermissionArea::Administrators->permission(PermissionAction::Edit))
            && $this->canActOn($actor, $target);
    }

    /**
     * Suspending, reactivating and resetting two-factor. Never on your own account.
     */
    public function suspend(User $actor, User $target): bool
    {
        return $actor->can(PermissionArea::Administrators->permission(PermissionAction::Manage))
            && $this->canActOn($actor, $target)
            && $actor->isNot($target);
    }

    /**
     * Whether the actor may give this role to an administrator. Only a Super Admin can grant Super Admin.
     */
    public function assignRole(User $actor, Role $role): bool
    {
        return ($this->create($actor) || $actor->can(PermissionArea::Administrators->permission(PermissionAction::Edit)))
            && (! $role->isLocked() || $actor->isSuperAdmin());
    }

    private function canActOn(User $actor, User $target): bool
    {
        return $target->isAdmin() && (! $target->isSuperAdmin() || $actor->isSuperAdmin());
    }
}
