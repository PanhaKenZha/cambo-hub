<?php

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AppealController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContentReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorSetupController;
use App\Http\Controllers\Admin\UserViolationController;
use Illuminate\Support\Facades\Route;

/*
| Admin routes are loaded from bootstrap/app.php with the "admin" URL prefix
| and "admin." route-name prefix.
*/

Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1');
Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:5,1');
Route::post('/logout', [AdminAuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('admin:enrolling')->group(function () {
    Route::get('/two-factor', [TwoFactorSetupController::class, 'prompt'])->name('two-factor.prompt');
    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'create'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorSetupController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/two-factor/setup/skip', [TwoFactorSetupController::class, 'skip'])->name('two-factor.skip');
});

/*
| Each area's "view" permission opens its pages; policies check the specific
| create / edit / delete / manage permission for every other action.
*/
Route::middleware('admin')->group(function () {
    Route::get('/', DashboardController::class)
        ->middleware('can:'.PermissionArea::Dashboard->permission(PermissionAction::View))
        ->name('dashboard');

    Route::middleware('can:'.PermissionArea::Administrators->permission(PermissionAction::View))->group(function () {
        Route::resource('users', AdminUserController::class)->except('destroy');
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('users.unsuspend');
        Route::post('/users/{user}/reset-two-factor', [AdminUserController::class, 'resetTwoFactor'])->name('users.reset-two-factor');
    });

    Route::middleware('can:'.PermissionArea::Roles->permission(PermissionAction::View))->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
        Route::post('/roles/{role}/reset', [RoleController::class, 'reset'])->name('roles.reset');
    });

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('can:'.PermissionArea::AuditLogs->permission(PermissionAction::View))
        ->name('audit-logs.index');

    Route::middleware('can:'.PermissionArea::Settings->permission(PermissionAction::View))->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/settings/{group}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/{group}', [SettingController::class, 'update'])->name('settings.update');
    });

    /*
    | Moderation. Policies also check the content's module permission (e.g. news.manage).
    */
    Route::middleware('can:'.PermissionArea::Approvals->permission(PermissionAction::View))->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{approvalRequest}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{approvalRequest}/assign', [ApprovalController::class, 'assign'])->name('approvals.assign');
        Route::post('/approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

        Route::get('/reports', [ContentReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [ContentReportController::class, 'show'])->name('reports.show');
        Route::post('/reports/{report}/resolve', [ContentReportController::class, 'resolve'])->name('reports.resolve');
        Route::post('/reports/{report}/dismiss', [ContentReportController::class, 'dismiss'])->name('reports.dismiss');

        Route::get('/violations', [UserViolationController::class, 'index'])->name('violations.index');
        Route::get('/violations/create', [UserViolationController::class, 'create'])->name('violations.create');
        Route::post('/violations', [UserViolationController::class, 'store'])->name('violations.store');
        Route::post('/violations/{violation}/overturn', [UserViolationController::class, 'overturn'])->name('violations.overturn');

        Route::get('/appeals', [AppealController::class, 'index'])->name('appeals.index');
        Route::get('/appeals/{appeal}', [AppealController::class, 'show'])->name('appeals.show');
        Route::post('/appeals/{appeal}/decide', [AppealController::class, 'decide'])->name('appeals.decide');
    });
});
