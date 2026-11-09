@extends('layouts.admin-minimal')
@section('title', 'Forgot password')
@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-surface px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-line sm:px-10 dark:shadow-black/30">
        <div class="absolute inset-x-0 top-0 flex h-1"><span class="w-2/3 bg-brand-600"></span><span class="w-1/3 bg-cambo-red"></span></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary-soft ring-1 ring-primary/15">
                <svg class="size-7 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.03 5.91c-.56-.1-1.16.02-1.56.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.82c0-.6.24-1.17.66-1.59l6.16-6.16c.41-.4.53-1 .43-1.56A6 6 0 1121.75 8.25z"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-ink">Forgot your password?</h1>
            <p class="mt-2 text-sm text-ink-muted">Enter your administrator email and we'll send you a link to choose a new password.</p>
        </div>

        <x-ui.error-summary class="mt-8" />

        <form method="POST" action="{{ route('admin.password.email') }}" class="mt-8 space-y-5" data-loading>
            @csrf
            <x-admin.input name="email" type="email" label="Email address" autocomplete="username" placeholder="you@cambohub.com" required autofocus />
            <x-admin.button class="w-full py-3" loading-text="Sending…">Send reset link</x-admin.button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('admin.login') }}" class="rounded font-medium text-ink-muted hover:text-primary">&larr; Back to sign in</a>
    </p>
</div>
@endsection
