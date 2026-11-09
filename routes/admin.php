<?php

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorSetupController;
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
});
