@extends('layouts.admin')
@php($isEditing = $author->exists)
@section('title', $isEditing ? 'Edit author' : 'New author')
@section('content')
@include('admin.news._nav')

<a href="{{ route('admin.news.authors.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Authors</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? 'Edit author' : 'New author' }}</h1>

<x-admin.card class="mt-6 max-w-4xl">
    <x-ui.error-summary class="mb-6" />
    <form method="POST" action="{{ $isEditing ? route('admin.news.authors.update', $author) : route('admin.news.authors.store') }}" enctype="multipart/form-data" class="space-y-6" data-loading>
        @csrf
        @if($isEditing) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.input name="name" label="Name (byline)" :value="$author->name" maxlength="100" required />
            <x-admin.input name="user_email" type="email" label="Linked account email (optional)" :value="$author->user?->email" hint="The account that writes as this author." />
        </div>
        <x-admin.translatable-field name="bio" label="Short bio" type="textarea" rows="2" :value="$author->bio" maxlength="500" />
        <div>
            <span class="block text-sm font-medium text-ink">Photo</span>
            <div class="mt-2 flex items-center gap-4">
                @if($author->avatarUrl())<img src="{{ $author->avatarUrl() }}" alt="" class="size-14 rounded-full object-cover ring-1 ring-line">@endif
                <input name="avatar" type="file" accept="image/*" aria-label="Photo"
                       class="block text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary">
            </div>
            @error('avatar')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2.5 text-sm text-ink">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 rounded accent-primary" @checked(old('is_active', $author->is_active))> Active (can be chosen as a byline)
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
                <x-admin.button variant="secondary" :href="route('admin.news.authors.index')">Cancel</x-admin.button>
                <x-admin.button loading-text="Saving…">{{ $isEditing ? 'Save' : 'Add author' }}</x-admin.button>
            </div>
        </div>
    </form>
    @if($isEditing)
        <form method="POST" action="{{ route('admin.news.authors.destroy', $author) }}" id="delete-form" onsubmit="return confirm('Delete this author? Their articles will have no byline.')">@csrf @method('DELETE')</form>
    @endif
</x-admin.card>
@endsection
