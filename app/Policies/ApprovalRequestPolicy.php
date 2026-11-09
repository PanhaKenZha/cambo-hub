<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksModuleAccess;

class ApprovalRequestPolicy
{
    use ChecksModuleAccess;

    public function viewAny(User $user): bool
    {
        return $this->canModerate($user, PermissionAction::View);
    }

    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        return $this->viewAny($user) && $this->canAccessModule($user, $approvalRequest->module, PermissionAction::View);
    }

    /**
     * Assign, approve or reject a pending request.
     */
    public function review(User $user, ApprovalRequest $approvalRequest): bool
    {
        return $approvalRequest->isPending()
            && $this->canModerate($user, PermissionAction::Manage)
            && $this->canAccessModule($user, $approvalRequest->module, PermissionAction::Manage);
    }
}
