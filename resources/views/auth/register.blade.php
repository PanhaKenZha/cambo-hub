@extends('layouts.app')
@section('auth-art', asset('images/content/platform-overview.webp'))
@section('title', 'Create account')
@section('page')
<x-ui.card as="section" aria-labelledby="register-title">
    <x-ui.eyebrow>YOUR CAMBO HUB ACCOUNT</x-ui.eyebrow>
    <h1 id="register-title" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Create your account</h1>
    <p class="mt-2 text-sm text-ink-muted">A few details and you're ready to go.</p>
    <x-ui.error-summary class="mt-6" />
    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5" data-loading>
        @csrf
        <x-ui.input name="name" label="Full name" autocomplete="name" maxlength="255" required autofocus />
        <x-ui.input name="email" type="email" label="Email address" autocomplete="email" required />
        <x-ui.input name="password" type="password" label="Password" hint="Use at least 8 characters." autocomplete="new-password" minlength="8" required />
        <x-ui.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />
        <x-ui.button class="w-full" size="lg" loading-text="Creating account…">Create account <span aria-hidden="true">→</span></x-ui.button>
    </form>
    <p class="mt-6 border-t border-line pt-5 text-center text-sm text-ink-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Sign in</a></p>
</x-ui.card>
@endsection
