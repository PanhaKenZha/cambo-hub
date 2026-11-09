@extends('layouts.app')
@section('title', 'Verify email')
@section('page')
<x-ui.card as="section" aria-labelledby="verify-title" class="text-center">
    <span class="mx-auto mb-5 grid size-14 place-items-center rounded-2xl bg-icon-tile"><img src="{{ asset('images/icons/messages-blue.svg') }}" width="28" height="28" alt=""></span>
    <x-ui.eyebrow>ONE MORE STEP</x-ui.eyebrow>
    <h1 id="verify-title" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Verify your email</h1>
    <p class="mt-2 text-sm text-ink-muted">Follow the link sent to <strong class="text-ink">{{ auth()->user()->email }}</strong> to open your dashboard. The link expires in 60 minutes.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-7" data-loading>
        @csrf
        <x-ui.button class="w-full" size="lg" loading-text="Sending…">Resend verification email</x-ui.button>
    </form>
    <div class="mt-6 flex flex-col items-center gap-3 border-t border-line pt-5 text-sm sm:flex-row sm:justify-between">
        <a href="{{ route('account.edit') }}" class="font-semibold text-primary hover:underline">Update your email address</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="secondary" size="sm">Sign out</x-ui.button></form>
    </div>
</x-ui.card>
@endsection
