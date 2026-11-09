<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LoginRecorder;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->challengedUser($request) === null) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticator $twoFactor, LoginRecorder $loginRecorder, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'required_without:code'],
        ]);
        $user = $this->challengedUser($request);

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        $usedRecoveryCode = filled($data['recovery_code'] ?? null);
        $isValid = $usedRecoveryCode
            ? $twoFactor->useRecoveryCode($user, $data['recovery_code'])
            : $twoFactor->verifyCode($user->two_factor_secret, $data['code'], $user->id);

        if (! $isValid) {
            $loginRecorder->recordFailure($user->email, 'invalid_two_factor_code', $user);

            if ($loginRecorder->recentFailureCount($user->email, config('admin.lockout_minutes')) >= config('admin.lockout_attempts')) {
                $request->session()->forget(AdminAuthController::PENDING_USER_SESSION_KEY);

                return redirect()->route('admin.login')->withErrors(['email' => 'Too many failed attempts. Please sign in again later.']);
            }

            throw ValidationException::withMessages([
                $usedRecoveryCode ? 'recovery_code' : 'code' => 'The code is invalid.',
            ]);
        }

        if ($request->session()->pull(AdminAuthController::PENDING_USER_SESSION_KEY) !== null) {
            Auth::login($user);
            $loginRecorder->recordSuccess($user);
        }

        $request->session()->regenerate();
        $request->session()->put(EnsureAdminSession::VERIFIED_SESSION_KEY, true);
        $request->session()->put(EnsureAdminSession::LAST_ACTIVITY_SESSION_KEY, now()->getTimestamp());

        if ($usedRecoveryCode) {
            $auditLogger->log('auth.recovery_code_used', $user, actor: $user);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * The administrator completing the challenge: either mid-login or already signed in through the public site.
     */
    private function challengedUser(Request $request): ?User
    {
        $pendingUserId = $request->session()->get(AdminAuthController::PENDING_USER_SESSION_KEY);
        $user = $pendingUserId !== null ? User::find($pendingUserId) : $request->user();

        if ($user === null || ! $user->isAdmin() || $user->isSuspended() || ! $user->hasTwoFactorEnabled()) {
            return null;
        }

        return $user;
    }
}
