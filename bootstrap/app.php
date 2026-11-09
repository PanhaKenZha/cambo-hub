<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureAdminSession;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\EnsurePlatformIsOnline;
use App\Http\Middleware\EnsureRegistrationIsOpen;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SetLocale runs before EnsurePlatformIsOnline so the maintenance page is shown in the visitor's language.
        $middleware->appendToGroup('web', [EnsureAccountIsActive::class, SetLocale::class, EnsurePlatformIsOnline::class]);
        $middleware->alias([
            'admin' => EnsureAdminSession::class,
            'module' => EnsureModuleEnabled::class,
            'registration.open' => EnsureRegistrationIsOpen::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
