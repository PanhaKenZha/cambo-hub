<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the admin area: requires an active administrator with confirmed
 * two-factor authentication, verified in this session, and not idle.
 *
 * Use `admin:enrolling` for the two-factor setup pages, which only require
 * an authenticated, active administrator.
 */
class EnsureAdminSession
{
    public const string VERIFIED_SESSION_KEY = 'admin.two_factor_verified';

    public const string LAST_ACTIVITY_SESSION_KEY = 'admin.last_activity';

    public const string SKIPPED_SETUP_SESSION_KEY = 'admin.two_factor_skipped';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $mode = 'verified'): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('admin.login'));
        }

        abort_unless($user->isAdmin(), Response::HTTP_FORBIDDEN);

        if ($user->isSuspended()) {
            return $this->signOut($request, 'Your account has been suspended.');
        }

        if ($this->hasBeenIdleTooLong($request)) {
            return $this->signOut($request, 'You were signed out after a period of inactivity.');
        }

        if ($mode !== 'enrolling') {
            if (! $user->hasTwoFactorEnabled()) {
                if (! $this->hasSkippedSetup($request)) {
                    return redirect()->route('admin.two-factor.prompt');
                }
            } elseif (! $request->session()->get(self::VERIFIED_SESSION_KEY)) {
                return redirect()->guest(route('admin.two-factor.challenge'));
            }
        }

        $request->session()->put(self::LAST_ACTIVITY_SESSION_KEY, now()->getTimestamp());

        return $next($request);
    }

    private function hasSkippedSetup(Request $request): bool
    {
        return config('admin.two_factor_skippable') && $request->session()->get(self::SKIPPED_SETUP_SESSION_KEY);
    }

    private function hasBeenIdleTooLong(Request $request): bool
    {
        $lastActivity = $request->session()->get(self::LAST_ACTIVITY_SESSION_KEY);

        return $lastActivity !== null
            && now()->getTimestamp() - $lastActivity > config('admin.idle_timeout_minutes') * 60;
    }

    private function signOut(Request $request, string $message): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->withErrors(['email' => $message]);
    }
}
