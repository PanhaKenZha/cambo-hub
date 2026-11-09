@extends('layouts.admin')
@section('title', 'Edit '.$admin->name)
@section('content')
<a href="{{ route('admin.users.show', $admin) }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; {{ $admin->name }}</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">Edit administrator</h1>

<x-admin.card class="mt-6 max-w-3xl">
    <x-ui.error-summary class="mb-6" />
    <form method="POST" action="{{ route('admin.users.update', $admin) }}" data-loading>
        @csrf @method('PUT')
        @include('admin.users._form')
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
            <x-admin.button variant="secondary" :href="route('admin.users.show', $admin)">Cancel</x-admin.button>
            <x-admin.button loading-text="Saving…">Save changes</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
