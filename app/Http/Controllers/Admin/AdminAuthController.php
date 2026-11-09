<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\User;
use App\Services\LoginRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public const string PENDING_USER_SESSION_KEY = 'admin.login.user_id';

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin() && $request->session()->get(EnsureAdminSession::VERIFIED_SESSION_KEY)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request, LoginRecorder $loginRecorder): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = Str::lower($credentials['email']);

        if ($loginRecorder->recentFailureCount($email, config('admin.lockout_minutes')) >= config('admin.lockout_attempts')) {
            $loginRecorder->recordFailure($email, 'locked_out');

            throw ValidationException::withMessages([
                'email' => 'Too many failed sign-in attempts. Try again in '.config('admin.lockout_minutes').' minutes.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            $loginRecorder->recordFailure($email, 'invalid_credentials', $user);

            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }

        if (! $user->isAdmin()) {
            $loginRecorder->recordFailure($email, 'not_admin', $user);

            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }

        if ($user->isSuspended()) {
            $loginRecorder->recordFailure($email, 'suspended', $user);

            throw ValidationException::withMessages(['email' => 'This account has been suspended.']);
        }

        $request->session()->regenerate();

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put(self::PENDING_USER_SESSION_KEY, $user->id);

            return redirect()->route('admin.two-factor.challenge');
        }

        Auth::login($user);
        $loginRecorder->recordSuccess($user);

        return redirect()->route('admin.two-factor.prompt');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }
}
