@extends('layouts.admin')
@php($isEditing = $category->exists)
@section('title', $isEditing ? 'Edit category' : 'New category')
@section('content')
@include('admin.news._nav')

<a href="{{ route('admin.news.categories.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Categories</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? 'Edit category' : 'New category' }}</h1>

<x-admin.card class="mt-6 max-w-4xl">
    <x-ui.error-summary class="mb-6" />
    <form method="POST" action="{{ $isEditing ? route('admin.news.categories.update', $category) : route('admin.news.categories.store') }}" class="space-y-6" data-loading>
        @csrf
        @if($isEditing) @method('PUT') @endif
        <x-admin.translatable-field name="name" label="Name" :value="$category->name" maxlength="60" required />
        <x-admin.translatable-field name="description" label="Description" type="textarea" rows="2" :value="$category->description" maxlength="300" />
        <div class="max-w-md">
            <x-admin.input name="slug" label="Address" :value="$category->slug" placeholder="from the English name" hint="Lowercase letters, numbers and dashes." />
        </div>
        <label class="flex items-center gap-2.5 text-sm text-ink">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 rounded accent-primary" @checked(old('is_active', $category->is_active))> Show this category on the website
        </label>
        <div class="flex items-center justify-between gap-3 border-t border-line pt-5">
            <div>
                @if($isEditing)
                    @can(\App\Enums\PermissionArea::News->permission(\App\Enums\PermissionAction::Delete))
                        <button type="submit" form="delete-form" class="cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete</button>
                    @endcan
                @endif
            </div>
            <div class="flex gap-3">
                <x-admin.button variant="secondary" :href="route('admin.news.categories.index')">Cancel</x-admin.button>
                <x-admin.button loading-text="Saving…">{{ $isEditing ? 'Save' : 'Add category' }}</x-admin.button>
            </div>
        </div>
    </form>
    @if($isEditing)
        <form method="POST" action="{{ route('admin.news.categories.destroy', $category) }}" id="delete-form" onsubmit="return confirm('Delete this category? Its articles become uncategorised.')">@csrf @method('DELETE')</form>
    @endif
</x-admin.card>
@endsection
