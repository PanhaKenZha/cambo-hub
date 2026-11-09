<?php

namespace App\Http\Middleware;

use App\Enums\PlatformModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hides a public module that administrators have turned off.
 *
 * Usage: Route::middleware('module:news')->group(...)
 */
class EnsureModuleEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(PlatformModule::from($module)->isEnabled(), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}
