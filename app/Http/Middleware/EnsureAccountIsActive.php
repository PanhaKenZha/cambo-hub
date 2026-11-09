<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out suspended accounts on every web request, including "remember me" sessions.
 */
class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginRoute = $request->is('admin', 'admin/*') ? 'admin.login' : 'login';

            return redirect()->route($loginRoute)->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
