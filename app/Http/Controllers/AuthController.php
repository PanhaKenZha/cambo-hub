<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginRecorder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $user = User::create($data);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function login(Request $request, LoginRecorder $loginRecorder): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $loginRecorder->recordFailure($credentials['email'], 'invalid_credentials', User::where('email', $credentials['email'])->first());

            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }
        $user = Auth::user();
        if ($user->isSuspended()) {
            Auth::logout();
            $loginRecorder->recordFailure($credentials['email'], 'suspended', $user);

            throw ValidationException::withMessages(['email' => 'This account has been suspended.']);
        }
        $request->session()->regenerate();
        $loginRecorder->recordSuccess($user);

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
