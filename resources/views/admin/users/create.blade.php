@extends('layouts.admin')
@section('title', 'Add administrator')
@section('content')
<a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Administrators</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Add administrator</h1>

<x-admin.card class="mt-6 max-w-3xl">
    <div class="mb-6 flex gap-3 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-800 ring-1 ring-brand-100">
        <svg class="mt-0.5 size-5 shrink-0 text-brand-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.25v2.75a.75.75 0 0 0 1.5 0V10a1 1 0 0 0-1-1H9Z" clip-rule="evenodd"/></svg>
        <p>The new administrator will receive an email with a link to set their password, then set up two-factor authentication at their first sign-in.</p>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @include('admin.users._form', ['admin' => null])
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <x-admin.button variant="secondary" :href="route('admin.users.index')">Cancel</x-admin.button>
            <x-admin.button>Create administrator</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
