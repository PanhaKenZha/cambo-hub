<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\PlatformModule;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\ContentReport;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Platform overview. Sections are only loaded when the administrator may see them.
     */
    public function __invoke(Request $request): View
    {
        $admin = $request->user();
        $canManageAdmins = $admin->can(PermissionArea::Administrators->permission(PermissionAction::View));
        $canViewAuditLogs = $admin->can(PermissionArea::AuditLogs->permission(PermissionAction::View));
        $role = $admin->primaryRole()?->load('permissions');

        return view('admin.dashboard', [
            'platformStats' => [
                'registeredUsers' => User::count(),
                'newUsersToday' => User::whereDate('created_at', today())->count(),
            ],
            'securityStats' => $canManageAdmins ? [
                'administrators' => User::whereHas('roles')->count(),
                'administratorsWithoutTwoFactor' => User::whereHas('roles')->whereNull('two_factor_confirmed_at')->count(),
                'suspendedAdministrators' => User::whereHas('roles')->whereNotNull('suspended_at')->count(),
                'signInsToday' => LoginHistory::where('successful', true)->whereDate('created_at', today())->count(),
                'failedSignInsLastDay' => LoginHistory::where('successful', false)->where('created_at', '>=', now()->subDay())->count(),
                'suspiciousSignInsLastWeek' => LoginHistory::where('is_suspicious', true)->where('created_at', '>=', now()->subWeek())->count(),
            ] : null,
            'moderationStats' => $admin->can(PermissionArea::Approvals->permission(PermissionAction::View)) ? [
                'pendingApprovals' => ApprovalRequest::pending()->whereIn('module', PlatformModule::visibleTo($admin))->count(),
                'openReports' => ContentReport::where('status', ReportStatus::Open)->count(),
            ] : null,
            'recentAuditLogs' => $canViewAuditLogs ? AuditLog::with('user')->latest('id')->limit(8)->get() : null,
            'recentSignIns' => $admin->loginHistories()->latest('id')->limit(6)->get(),
            'areaAccess' => collect(PermissionArea::cases())
                ->mapWithKeys(fn (PermissionArea $area): array => [$area->label() => $role?->accessLevel($area) ?? 'none'])
                ->reject(fn (string $level): bool => $level === 'none'),
        ]);
    }
}
