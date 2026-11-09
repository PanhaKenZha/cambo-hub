@extends('layouts.admin-minimal')
@section('title', 'Set up two-factor authentication')
@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-8 flex items-end justify-between gap-6">
        <div>
            <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">
                <span class="size-1.5 rounded-full bg-brand-500"></span>
                Account security
            </div>
            <h1 class="max-w-xl text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Protect your admin account</h1>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-500 sm:text-base">Set up an authenticator app to add a second layer of protection to Cambo Hub.</p>
        </div>
        <div class="hidden shrink-0 items-center gap-2 pb-1 text-xs font-semibold text-slate-400 sm:flex" aria-label="Setup progress">
            <span class="flex size-7 items-center justify-center rounded-full bg-brand-700 text-white">1</span>
            <span class="h-px w-8 bg-slate-300"></span>
            <span class="flex size-7 items-center justify-center rounded-full bg-slate-200 text-slate-500">2</span>
        </div>
    </div>

    <ol class="grid gap-5 lg:grid-cols-[1.12fr_0.88fr]">
    <li class="rounded-2xl bg-white p-6 shadow-[0_12px_35px_-24px_rgba(15,23,42,0.45)] ring-1 ring-slate-200 sm:p-7">
        <div class="flex items-start gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white shadow-sm shadow-brand-700/25">1</span>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-950">Scan the QR code</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">Use Google Authenticator, Microsoft Authenticator, 1Password, or another compatible app.</p>
            </div>
        </div>
        <div class="mt-7 flex flex-col items-center gap-6 sm:flex-row sm:items-center">
            <div class="shrink-0 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 [&_svg]:size-40">{!! $qrCodeSvg !!}</div>
            <div class="w-full min-w-0">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Can't scan?</p>
                <p class="mt-1 text-sm text-slate-500">Enter this setup key in your app.</p>
                <p class="mt-3 rounded-xl bg-slate-50 px-3.5 py-3 font-mono text-sm leading-relaxed tracking-wider break-all text-slate-800 ring-1 ring-slate-200 select-all">{{ trim(chunk_split($secret, 4, ' ')) }}</p>
                <p class="mt-2 text-xs text-slate-400">Click the key to select and copy it.</p>
            </div>
        </div>
    </li>

    <li class="rounded-2xl bg-white p-6 shadow-[0_12px_35px_-24px_rgba(15,23,42,0.45)] ring-1 ring-slate-200 sm:p-7">
        <div class="flex items-start gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white shadow-sm shadow-brand-700/25">2</span>
            <div>
                <h2 class="text-base font-semibold text-slate-950">Enter the 6-digit code</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">Type the code currently shown for Cambo Hub.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.two-factor.setup') }}" class="mt-8 space-y-5">
            @csrf
            <x-admin.input name="code" label="Authentication code" inputmode="numeric" pattern="[0-9 ]*" maxlength="7" autocomplete="one-time-code" placeholder="000000" class="text-center font-mono text-2xl! tracking-[0.5em]" required autofocus />
            <x-admin.button class="w-full py-3.5">Confirm and enable <span aria-hidden="true">→</span></x-admin.button>
        </form>
        <p class="mt-5 flex items-start gap-2 text-xs leading-relaxed text-slate-400">
            <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-7-3a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a1 1 0 0 0 0 2v2a1 1 0 0 0 2 0v-3a1 1 0 0 0-1-1H9Z" clip-rule="evenodd"/></svg>
            Your authenticator code refreshes every 30 seconds.
        </p>
    </li>
</ol>

<div class="mt-7 flex items-center justify-between gap-4 border-t border-slate-200/80 pt-5 text-sm">
    <a href="{{ route('admin.two-factor.prompt') }}" class="font-medium text-slate-500 hover:text-slate-800">&larr; Back</a>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="cursor-pointer font-medium text-slate-500 hover:text-slate-800">Sign out</button>
    </form>
</div>
</div>
@endsection
