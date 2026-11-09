<?php

namespace App\Policies;

use App\Enums\PermissionAction;
use App\Models\ContentReport;
use App\Models\User;
use App\Policies\Concerns\ChecksModuleAccess;

class ContentReportPolicy
{
    use ChecksModuleAccess;

    public function viewAny(User $user): bool
    {
        return $this->canModerate($user, PermissionAction::View);
    }

    public function view(User $user, ContentReport $contentReport): bool
    {
        return $this->viewAny($user) && $this->canAccessModule($user, $contentReport->module, PermissionAction::View);
    }

    /**
     * Resolve or dismiss an open report.
     */
    public function handle(User $user, ContentReport $contentReport): bool
    {
        return $contentReport->isOpen()
            && $this->canModerate($user, PermissionAction::Manage)
            && $this->canAccessModule($user, $contentReport->module, PermissionAction::Manage);
    }
}
