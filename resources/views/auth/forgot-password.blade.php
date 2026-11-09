@extends('layouts.app')
@section('title', 'Reset password')
@section('page')
<x-ui.card as="section" aria-labelledby="forgot-title">
    <span class="mb-5 grid size-12 place-items-center rounded-2xl bg-icon-tile"><img src="{{ asset('images/icons/shield-blue.svg') }}" width="24" height="24" alt=""></span>
    <x-ui.eyebrow>ACCOUNT RECOVERY</x-ui.eyebrow>
    <h1 id="forgot-title" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Forgot your password?</h1>
    <p class="mt-2 text-sm text-ink-muted">Enter your email and we'll send you a link to choose a new password.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5" data-loading>
        @csrf
        <x-ui.input name="email" type="email" label="Email address" autocomplete="email" required autofocus />
        <x-ui.button class="w-full" size="lg" loading-text="Sending…">Send reset link</x-ui.button>
    </form>
    <p class="mt-6 border-t border-line pt-5 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-primary hover:underline"><span aria-hidden="true">←</span> Back to sign in</a></p>
</x-ui.card>
@endsection
