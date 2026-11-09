@extends('layouts.admin-minimal')
@section('title', 'Secure your account')
@section('content')
<div class="mx-auto w-full max-w-md text-center">
    <div class="mx-auto flex size-36 items-center justify-center rounded-full bg-brand-100/70 dark:bg-brand-800/40">
        <svg class="size-24" viewBox="0 0 80 80" fill="none" aria-hidden="true">
            <path d="M40 6 66 16v20c0 17-11 31-26 37C25 67 14 53 14 36V16L40 6Z" fill="var(--color-brand-200)" stroke="var(--color-brand-500)" stroke-width="2.5" stroke-linejoin="round"/>
            <path d="M40 13 59 20.5V36c0 13-8 24-19 29-11-5-19-16-19-29V20.5L40 13Z" fill="var(--color-brand-100)"/>
            <rect x="29" y="36" width="22" height="17" rx="3" fill="#fff" stroke="var(--color-cambo-red)" stroke-width="2.5"/>
            <path d="M33.5 36v-4.5a6.5 6.5 0 0 1 13 0V36" stroke="var(--color-cambo-red)" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="40" cy="43" r="2.2" fill="var(--color-cambo-red)"/>
            <path d="M40 45v3.5" stroke="var(--color-cambo-red)" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
    </div>

    <h1 class="mt-10 text-2xl font-bold tracking-tight text-ink">Let's make your account extra secure</h1>
    <p class="mx-auto mt-4 max-w-sm text-sm leading-relaxed text-ink-muted">
        Two-factor authentication adds an extra layer of protection to your administrator account — so even if someone gets your password, they can't get in.
    </p>

    <div class="mx-auto mt-8 max-w-sm space-y-4">
        <x-admin.button :href="route('admin.two-factor.setup')" class="w-full py-3">Set up two-factor authentication</x-admin.button>

        @if($canSkip)
            <form method="POST" action="{{ route('admin.two-factor.skip') }}" data-loading>
                @csrf
                <button type="submit" class="cursor-pointer rounded-md px-3 py-1.5 text-sm font-medium text-ink-muted hover:bg-surface-muted hover:text-ink aria-disabled:cursor-progress aria-disabled:opacity-70">Skip for now</button>
            </form>
        @else
            <p class="text-xs text-ink-muted">Two-factor authentication is required for all administrators.</p>
        @endif
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="cursor-pointer rounded text-sm font-medium text-ink-muted hover:text-ink">Sign out</button>
        </form>
    </div>
</div>
@endsection
