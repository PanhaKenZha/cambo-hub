@extends('layouts.app')
@section('auth-art', asset('images/content/user-profile.webp'))
@section('title', 'Sign in')
@section('page')
<x-ui.card as="section" aria-labelledby="login-title">
    <x-ui.eyebrow>CLIENT ACCOUNT · WELCOME BACK</x-ui.eyebrow>
    <h1 id="login-title" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Sign in to your account</h1>
    <p class="mt-2 text-sm text-ink-muted">Sign in to your personal Cambo Hub account.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" data-loading>
        @csrf
        <x-ui.input name="email" type="email" label="Email address" autocomplete="email" required autofocus />
        <div>
            <x-ui.input name="password" type="password" label="Password" autocomplete="current-password" required />
            <a href="{{ route('password.request') }}" class="mt-2 inline-block text-xs font-semibold text-primary hover:underline">Forgot password?</a>
        </div>
        <x-ui.checkbox name="remember" :checked="(bool) old('remember')">Keep me signed in</x-ui.checkbox>
        <x-ui.button class="w-full" size="lg" loading-text="Signing in…">Sign in <span aria-hidden="true">→</span></x-ui.button>
    </form>
    <p class="mt-6 border-t border-line pt-5 text-center text-sm text-ink-muted">New here? <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Create an account</a></p>
</x-ui.card>
@endsection
