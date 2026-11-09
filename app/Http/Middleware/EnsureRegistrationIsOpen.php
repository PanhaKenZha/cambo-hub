<?php

namespace App\Http\Middleware;

use App\Services\PlatformSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes the sign-up page when administrators turn registration off.
 */
class EnsureRegistrationIsOpen
{
    public function __construct(private PlatformSettings $settings) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->get('registration.enabled')) {
            return $next($request);
        }

        return redirect()->route('login')->with('status', $this->settings->get('registration.closed_message'));
    }
}
