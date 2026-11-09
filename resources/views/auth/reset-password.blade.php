@extends('layouts.app')
@section('title', 'New password')
@section('page')
<x-ui.card as="section" aria-labelledby="reset-title">
    <x-ui.eyebrow>A FRESH START</x-ui.eyebrow>
    <h1 id="reset-title" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Choose a new password</h1>
    <p class="mt-2 text-sm text-ink-muted">Use at least 8 characters for your new password.</p>
    <x-ui.error-summary class="mt-6" />
    <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5" data-loading>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input name="email" type="email" label="Email address" :value="$email" autocomplete="email" required />
        <x-ui.input name="password" type="password" label="New password" autocomplete="new-password" minlength="8" required />
        <x-ui.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />
        <x-ui.button class="w-full" size="lg" loading-text="Saving…">Reset password</x-ui.button>
    </form>
    <p class="mt-6 border-t border-line pt-5 text-center text-sm"><a href="{{ route('password.request') }}" class="font-semibold text-primary hover:underline">Request a new link</a></p>
</x-ui.card>
@endsection
