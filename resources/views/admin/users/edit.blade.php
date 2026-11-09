@extends('layouts.admin')
@section('title', 'Edit '.$admin->name)
@section('content')
<a href="{{ route('admin.users.show', $admin) }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; {{ $admin->name }}</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Edit administrator</h1>

<x-admin.card class="mt-6 max-w-3xl">
    <form method="POST" action="{{ route('admin.users.update', $admin) }}">
        @csrf @method('PUT')
        @include('admin.users._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <x-admin.button variant="secondary" :href="route('admin.users.show', $admin)">Cancel</x-admin.button>
            <x-admin.button>Save changes</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
