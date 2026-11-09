<?php

namespace App\Http\Middleware;

use App\Services\PlatformSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows the maintenance page on the public website while maintenance mode is on.
 * The admin panel, the health check and signed-in administrators are not affected.
 */
class EnsurePlatformIsOnline
{
    public function __construct(private PlatformSettings $settings) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->get('maintenance.enabled')
            || $request->is('admin', 'admin/*', 'up')
            || $request->user()?->isAdmin()) {
            return $next($request);
        }

        return response()->view('maintenance', [
            'message' => $this->settings->get('maintenance.message'),
            'siteName' => $this->settings->get('general.site_name'),
        ], Response::HTTP_SERVICE_UNAVAILABLE)->header('Retry-After', '3600');
    }
}
