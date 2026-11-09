@extends('layouts.admin')
@section('title', 'Add administrator')
@section('content')
<a href="{{ route('admin.users.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Administrators</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">Add administrator</h1>

<x-admin.card class="mt-6 max-w-3xl">
    <div class="mb-6 flex gap-3 rounded-xl bg-primary-soft px-4 py-3 text-sm text-ink ring-1 ring-primary/15">
        <svg class="mt-0.5 size-5 shrink-0 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.25v2.75a.75.75 0 0 0 1.5 0V10a1 1 0 0 0-1-1H9Z" clip-rule="evenodd"/></svg>
        <p>The new administrator will receive an email with a link to set their password, then set up two-factor authentication at their first sign-in.</p>
    </div>
    <x-ui.error-summary class="mb-6" />
    <form method="POST" action="{{ route('admin.users.store') }}" data-loading>
        @csrf
        @include('admin.users._form', ['admin' => null])
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
            <x-admin.button variant="secondary" :href="route('admin.users.index')">Cancel</x-admin.button>
            <x-admin.button loading-text="Creating…">Create administrator</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
