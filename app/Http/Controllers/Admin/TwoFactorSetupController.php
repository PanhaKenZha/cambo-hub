<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSession;
use App\Services\AuditLogger;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorSetupController extends Controller
{
    private const string SETUP_SECRET_SESSION_KEY = 'admin.two_factor_setup_secret';

    /**
     * Introductory page shown to administrators who have not set up two-factor authentication.
     */
    public function prompt(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.two-factor-prompt', ['canSkip' => config('admin.two_factor_skippable')]);
    }

    public function create(Request $request, TwoFactorAuthenticator $twoFactor): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        $secret = $request->session()->get(self::SETUP_SECRET_SESSION_KEY);

        if ($secret === null) {
            $secret = $twoFactor->generateSecret();
            $request->session()->put(self::SETUP_SECRET_SESSION_KEY, $secret);
        }

        return view('admin.auth.two-factor-setup', [
            'qrCodeSvg' => $twoFactor->qrCodeSvg($user, $secret),
            'secret' => $secret,
        ]);
    }

    /**
     * Let an administrator use the admin area for this session without two-factor, when allowed by config.
     */
    public function skip(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(config('admin.two_factor_skippable'), Response::HTTP_FORBIDDEN);

        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        $request->session()->put(EnsureAdminSession::SKIPPED_SETUP_SESSION_KEY, true);
        $auditLogger->log('auth.two_factor_skipped', $request->user());

        return redirect()->intended(route('admin.dashboard'));
    }

    public function store(Request $request, TwoFactorAuthenticator $twoFactor, AuditLogger $auditLogger): View|RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();
        $secret = $request->session()->get(self::SETUP_SECRET_SESSION_KEY);

        if ($user->hasTwoFactorEnabled() || $secret === null) {
            return redirect()->route('admin.two-factor.setup');
        }

        if (! $twoFactor->verifyCode($secret, $data['code'], $user->id)) {
            throw ValidationException::withMessages(['code' => 'The code is invalid. Check your authenticator app and try again.']);
        }

        $recoveryCodes = $twoFactor->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget(self::SETUP_SECRET_SESSION_KEY);
        $request->session()->regenerate();
        $request->session()->put(EnsureAdminSession::VERIFIED_SESSION_KEY, true);
        $auditLogger->log('auth.two_factor_enabled', $user);

        return view('admin.auth.recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }
}
