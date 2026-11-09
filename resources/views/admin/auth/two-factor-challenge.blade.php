@extends('layouts.admin-minimal')
@section('title', 'Two-factor verification')
@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-slate-200 sm:px-10">
        <div class="absolute inset-x-0 top-0 flex h-1"><span class="w-2/3 bg-brand-600"></span><span class="w-1/3 bg-cambo-red"></span></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 ring-1 ring-brand-100">
                <svg class="size-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Enter your authentication code</h1>
            <p class="mt-2 text-sm text-slate-500">Open your authenticator app and enter the 6-digit code for Cambo Hub.</p>
        </div>

        <form method="POST" action="{{ route('admin.two-factor.challenge') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="code" class="sr-only">Authentication code</label>
                <input id="code" name="code" inputmode="numeric" pattern="[0-9 ]*" maxlength="7" autocomplete="one-time-code" placeholder="000000" autofocus
                       @class([
                           'block w-full rounded-xl border-0 bg-slate-50 px-4 py-4 text-center font-mono text-3xl tracking-[0.5em] text-slate-900 ring-1 ring-inset placeholder:text-slate-300 focus:bg-white focus:ring-2 focus:outline-none focus:ring-inset',
                           'ring-red-400 focus:ring-red-500' => $errors->has('code'),
                           'ring-slate-200 focus:ring-brand-500' => ! $errors->has('code'),
                       ])>
                @error('code')<p class="mt-2 text-center text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>
            <x-admin.button class="w-full py-3">Verify</x-admin.button>
        </form>

        <div class="my-7 flex items-center gap-3 text-xs text-slate-400">
            <span class="h-px flex-1 bg-slate-200"></span>
            or
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <details class="group" @if($errors->has('recovery_code')) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-center gap-1.5 text-sm font-medium text-slate-600 hover:text-brand-700">
                Lost your device? Use a recovery code
                <svg class="size-4 text-slate-400 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
            </summary>
            <form method="POST" action="{{ route('admin.two-factor.challenge') }}" class="mt-4 space-y-3">
                @csrf
                <x-admin.input name="recovery_code" label="Recovery code" autocomplete="off" placeholder="xxxxxxxxxx-xxxxxxxxxx" class="font-mono" />
                <x-admin.button variant="secondary" class="w-full">Use recovery code</x-admin.button>
            </form>
        </details>
    </div>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('admin.login') }}" class="font-medium text-slate-500 hover:text-brand-700">&larr; Back to sign in</a>
    </p>
</div>
@endsection
