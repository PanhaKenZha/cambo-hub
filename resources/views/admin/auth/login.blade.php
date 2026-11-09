@extends('layouts.admin-minimal')
@section('title', 'Sign in')
@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-slate-200 sm:px-10">
        <div class="absolute inset-x-0 top-0 flex h-1"><span class="w-2/3 bg-brand-600"></span><span class="w-1/3 bg-cambo-red"></span></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 ring-1 ring-brand-100">
                <svg class="size-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Sign in to the admin panel</h1>
            <p class="mt-2 text-sm text-slate-500">Authorized administrators only. All activity is logged.</p>
        </div>

        <form method="POST" action="{{ route('admin.login') }}" class="mt-8 space-y-5">
            @csrf
            <x-admin.input name="email" type="email" label="Email address" autocomplete="username" placeholder="you@cambohub.com" required autofocus />
            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-800">Forgot password?</a>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="mt-1.5 block w-full rounded-lg border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-slate-300 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500 focus:outline-none focus:ring-inset">
                @error('password')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>
            <x-admin.button class="w-full py-3">
                Continue
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.72-3.72a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
            </x-admin.button>
        </form>
    </div>

    <p class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400">
        <svg class="size-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
        Protected by two-factor authentication
    </p>
</div>
@endsection
