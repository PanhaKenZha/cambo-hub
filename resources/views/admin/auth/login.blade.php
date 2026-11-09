@extends('layouts.admin-minimal')
@section('title', 'Sign in')
@section('content')
@php($passwordError = $errors->first('password') ?: null)
<div class="mx-auto w-full max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-surface px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-line sm:px-10 dark:shadow-black/30">
        <div class="absolute inset-x-0 top-0 flex h-1"><span class="w-2/3 bg-brand-600"></span><span class="w-1/3 bg-cambo-red"></span></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary-soft ring-1 ring-primary/15">
                <svg class="size-7 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-ink">Sign in to the admin panel</h1>
            <p class="mt-2 text-sm text-ink-muted">Authorized administrators only. All activity is logged.</p>
        </div>

        <x-ui.error-summary class="mt-8" />

        <form method="POST" action="{{ route('admin.login') }}" class="mt-8 space-y-5" data-loading>
            @csrf
            <x-admin.input name="email" type="email" label="Email address" autocomplete="username" placeholder="you@cambohub.com" required autofocus />
            <div>
                <div class="flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-medium text-ink">Password<span class="ml-0.5 text-accent" aria-hidden="true">*</span></label>
                    <a href="{{ route('password.request') }}" class="rounded text-sm font-medium text-primary hover:text-primary-hover">Forgot password?</a>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       @if($passwordError) aria-invalid="true" aria-describedby="password-error" @endif
                       class="form-control mt-1.5 rounded-lg shadow-sm">
                @if($passwordError)
                    <p id="password-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $passwordError }}</p>
                @endif
            </div>
            <x-admin.button class="w-full py-3" loading-text="Signing in…">
                Continue
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.72-3.72a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
            </x-admin.button>
        </form>
    </div>

    <p class="mt-6 flex items-center justify-center gap-2 text-xs text-ink-subtle">
        <svg class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
        Protected by two-factor authentication
    </p>
</div>
@endsection
