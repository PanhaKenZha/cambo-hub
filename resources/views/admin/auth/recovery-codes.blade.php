@extends('layouts.admin-minimal')
@section('title', 'Recovery codes')
@section('content')
<div class="mx-auto w-full max-w-xl">
    <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-xl shadow-brand-900/5 ring-1 ring-slate-200 sm:px-10">
        <div class="absolute inset-x-0 top-0 h-1 bg-emerald-500"></div>

        <div class="text-center">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-100">
                <svg class="size-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.04A11.96 11.96 0 013.6 6 12 12 0 003 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29z"/></svg>
            </span>
            <p class="mt-5 text-xs font-semibold tracking-[0.2em] text-emerald-600 uppercase">Two-factor enabled</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Save your recovery codes</h1>
            <p class="mt-2 text-sm text-slate-500">Each code works once if you lose access to your authenticator app.</p>
        </div>

        <ul class="mt-8 grid grid-cols-1 gap-2 rounded-2xl bg-slate-50 p-4 font-mono text-sm text-slate-800 ring-1 ring-slate-200 select-all sm:grid-cols-2">
            @foreach($recoveryCodes as $recoveryCode)
                <li class="rounded-lg bg-white px-3 py-2 text-center whitespace-nowrap ring-1 ring-slate-200">{{ $recoveryCode }}</li>
            @endforeach
        </ul>

        <div class="mt-5 flex gap-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-600/20">
            <svg class="mt-0.5 size-5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.49 2.87a1.75 1.75 0 0 1 3.02 0l6.28 10.88A1.75 1.75 0 0 1 16.28 16.4H3.72a1.75 1.75 0 0 1-1.51-2.63L8.49 2.87ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            <p>Store them somewhere safe, such as a password manager. They will not be shown again.</p>
        </div>

        <x-admin.button :href="route('admin.dashboard')" class="mt-8 w-full py-3">I have saved my codes &rarr;</x-admin.button>
    </div>
</div>
@endsection
