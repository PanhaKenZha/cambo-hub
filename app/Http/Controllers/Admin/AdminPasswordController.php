<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Forgot / reset password for administrators, in the admin panel's own pages.
 */
class AdminPasswordController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Send a reset link to administrator accounts only. The response is the same either way,
     * so the form cannot be used to discover which emails belong to administrators.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', Str::lower($data['email']))->first();

        if ($user?->isAdmin()) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', 'If an administrator account exists for that email, a password reset link has been sent.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->numbers()],
        ]);

        $resetUser = null;
        $status = Password::reset($data, function (User $user, string $password) use (&$resetUser): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
            $resetUser = $user;
        });

        if ($status !== Password::PasswordReset) {
            return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
        }

        $auditLogger->log('auth.password_reset', $resetUser, actor: $resetUser);

        return redirect()->route('admin.login')->with('status', 'Your password has been changed. You can now sign in.');
    }
}
