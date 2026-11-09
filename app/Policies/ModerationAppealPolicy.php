<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Enums\PlatformModule;
use App\Models\ApprovalRequest;
use App\Models\ModerationAppeal;
use App\Models\User;
use App\Policies\Concerns\ChecksModuleAccess;

class ModerationAppealPolicy
{
    use ChecksModuleAccess;

    public function viewAny(User $user): bool
    {
        return $this->canModerate($user, PermissionAction::View);
    }

    public function view(User $user, ModerationAppeal $appeal): bool
    {
        return $this->viewAny($user) && $this->canAccessModule($user, $this->module($appeal), PermissionAction::View);
    }

    /**
     * Appeals are decided by someone other than the moderator who made the original
     * decision. Super Admins are the only exception.
     */
    public function decide(User $user, ModerationAppeal $appeal): bool
    {
        return $appeal->isPending()
            && $this->canModerate($user, PermissionAction::Manage)
            && $this->canAccessModule($user, $this->module($appeal), PermissionAction::Manage)
            && ($user->id !== $appeal->originalDeciderId() || $user->isSuperAdmin());
    }

    private function module(ModerationAppeal $appeal): ?PlatformModule
    {
        return $appeal->appealable instanceof ApprovalRequest ? $appeal->appealable->module : null;
    }
}
