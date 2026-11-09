@extends('layouts.admin-minimal')
@section('title', 'Choose a new password')
@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-surface px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-line sm:px-10 dark:shadow-black/30">
        <div class="absolute inset-x-0 top-0 flex h-1"><span class="w-2/3 bg-brand-600"></span><span class="w-1/3 bg-cambo-red"></span></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary-soft ring-1 ring-primary/15">
                <svg class="size-7 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-ink">Choose a new password</h1>
            <p class="mt-2 text-sm text-ink-muted">Administrator passwords need at least 12 characters, including letters and numbers.</p>
        </div>

        <x-ui.error-summary class="mt-8" />

        <form method="POST" action="{{ route('admin.password.update') }}" class="mt-8 space-y-5" data-loading>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-admin.input name="email" type="email" label="Email address" :value="$email" autocomplete="username" required />
            <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" minlength="12" required autofocus />
            <x-admin.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" minlength="12" required />
            <x-admin.button class="w-full py-3" loading-text="Saving…">Save new password</x-admin.button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('admin.password.request') }}" class="rounded font-medium text-ink-muted hover:text-primary">Link expired? Request a new one</a>
    </p>
</div>
@endsection
