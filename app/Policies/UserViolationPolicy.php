<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Models\User;
use App\Models\UserViolation;
use App\Policies\Concerns\ChecksModuleAccess;

class UserViolationPolicy
{
    use ChecksModuleAccess;

    public function viewAny(User $user): bool
    {
        return $this->canModerate($user, PermissionAction::View);
    }

    public function create(User $user): bool
    {
        return $this->canModerate($user, PermissionAction::Manage);
    }

    public function overturn(User $user, UserViolation $userViolation): bool
    {
        return $userViolation->isActive() && $this->canModerate($user, PermissionAction::Manage);
    }
}
