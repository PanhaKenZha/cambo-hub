<?php

namespace App\Http\Controllers;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    /**
     * Stop with 403 unless the signed-in administrator has the permission, e.g. "cms.edit".
     */
    protected function authorizePermission(PermissionArea $area, PermissionAction $action): void
    {
        abort_unless(request()->user()?->can($area->permission($action)), Response::HTTP_FORBIDDEN);
    }
}
