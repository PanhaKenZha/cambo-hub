@extends('layouts.admin')
@php
    $isEditing = $menuItem->exists;
    $linkType = old('link_type', $menuItem->page_id ? 'page' : 'url');
@endphp
@section('title', $isEditing ? 'Edit menu item' : 'Add menu item')
@section('content')
<a href="{{ route('admin.cms.menus.index', ['location' => $menuItem->location->value]) }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; {{ $menuItem->location->label() }}</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $isEditing ? 'Edit menu item' : 'Add menu item' }}</h1>

<x-admin.card class="mt-6 max-w-4xl">
    <form method="POST" action="{{ $isEditing ? route('admin.cms.menus.update', $menuItem) : route('admin.cms.menus.store') }}" class="space-y-6">
        @csrf
        @if($isEditing) @method('PUT') @endif
        <input type="hidden" name="location" value="{{ $menuItem->location->value }}">

        <x-admin.translatable-field name="label" label="Label" :value="$menuItem->label" maxlength="60" required />

        <fieldset>
            <legend class="text-sm font-medium text-slate-700">Links to</legend>
            <div class="mt-2 flex gap-2">
                @foreach(['page' => 'A website page', 'url' => 'A web address'] as $value => $label)
                    <label class="cursor-pointer rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-slate-300 has-checked:bg-brand-50 has-checked:text-brand-700 has-checked:ring-brand-500">
                        <input type="radio" name="link_type" value="{{ $value }}" class="sr-only" @checked($linkType === $value)
                               onchange="document.getElementById('link-page').hidden = this.value !== 'page'; document.getElementById('link-url').hidden = this.value !== 'url'">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div id="link-page" @if($linkType !== 'page') hidden @endif class="max-w-xl">
            <x-admin.select name="page_id" label="Page">
                <option value="">Choose a page…</option>
                @foreach($pages as $page)
                    <option value="{{ $page->id }}" @selected((int) old('page_id', $menuItem->page_id) === $page->id)>{{ $page->translate('title', 'en') }} — /pages/{{ $page->slug }} ({{ $page->stateLabel() }})</option>
                @endforeach
            </x-admin.select>
        </div>
        <div id="link-url" @if($linkType !== 'url') hidden @endif class="max-w-xl">
            <x-admin.input name="url" label="Web address" :value="$menuItem->url" placeholder="/jobs or https://example.com" hint="Start with / for pages on Cambo Hub, or https:// for other websites." />
        </div>

        <div class="max-w-xl">
            <x-admin.select name="parent_id" label="Position">
                <option value="">Top level</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((int) old('parent_id', $menuItem->parent_id) === $parent->id)>Under “{{ $parent->translate('label', 'en') }}”</option>
                @endforeach
            </x-admin.select>
        </div>

        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2.5 text-sm text-slate-700">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" class="size-4 rounded accent-brand-600" @checked(old('is_visible', $menuItem->is_visible))> Show in the menu
            </label>
            <label class="flex items-center gap-2.5 text-sm text-slate-700">
                <input type="hidden" name="opens_in_new_tab" value="0">
                <input type="checkbox" name="opens_in_new_tab" value="1" class="size-4 rounded accent-brand-600" @checked(old('opens_in_new_tab', $menuItem->opens_in_new_tab))> Open in a new tab
            </label>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
            <div>
                @if($isEditing)
                    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
                        <button type="submit" form="delete-form" class="cursor-pointer text-sm font-semibold text-red-600 hover:text-red-800">Delete</button>
                    @endcan
                @endif
            </div>
            <div class="flex gap-3">
                <x-admin.button variant="secondary" :href="route('admin.cms.menus.index', ['location' => $menuItem->location->value])">Cancel</x-admin.button>
                <x-admin.button>{{ $isEditing ? 'Save' : 'Add item' }}</x-admin.button>
            </div>
        </div>
    </form>
    @if($isEditing)
        <form method="POST" action="{{ route('admin.cms.menus.destroy', $menuItem) }}" id="delete-form" onsubmit="return confirm('Delete this menu item{{ $menuItem->children()->exists() ? ' and its sub-items' : '' }}?')">@csrf @method('DELETE')</form>
    @endif
</x-admin.card>
@endsection
