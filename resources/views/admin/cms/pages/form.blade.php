@extends('layouts.admin')
@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $isEditing = $page !== null;
    $canPublish = auth()->user()->can(PermissionArea::WebsiteContent->permission(PermissionAction::Manage));
    $status = old('status', $page?->status->value ?? 'draft');
@endphp
@section('title', $isEditing ? 'Edit page' : 'New page')
@section('content')
<a href="{{ route('admin.cms.pages.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Pages</a>

<div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $isEditing ? $page->translate('title', 'en') : 'New page' }}</h1>
        @if($isEditing)<x-admin.badge :color="$page->stateColor()">{{ $page->stateLabel() }}</x-admin.badge>@endif
    </div>
    @if($isEditing)
        <div class="flex gap-2">
            <x-admin.button variant="secondary" :href="route('admin.cms.pages.preview', $page)" target="_blank">Preview EN</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.cms.pages.preview', [$page, 'lang' => 'km'])" target="_blank">Preview KM</x-admin.button>
        </div>
    @endif
</div>

<form method="POST" action="{{ $isEditing ? route('admin.cms.pages.update', $page) : route('admin.cms.pages.store') }}" id="page-form" class="mt-6 grid gap-6 xl:grid-cols-[1fr_20rem]">
    @csrf
    @if($isEditing) @method('PUT') @endif

    <div class="min-w-0 space-y-6">
        <x-admin.card title="Content">
            <div class="space-y-5">
                <x-admin.translatable-field name="title" label="Title" :value="$page?->title" maxlength="200" required />
                <x-admin.translatable-field name="body" label="Page text" type="textarea" rows="16" :value="$page?->body"
                    hint="Formatting with Markdown: ## Heading, **bold**, *italic*, - list item, [link text](https://example.com). HTML is not allowed." />
            </div>
        </x-admin.card>

        <x-admin.card title="Search engines (SEO)" description="Optional. The page title and website defaults are used when empty.">
            <div class="space-y-5">
                <x-admin.translatable-field name="meta_title" label="SEO title" :value="$page?->meta_title" maxlength="70" />
                <x-admin.translatable-field name="meta_description" label="SEO description" type="textarea" rows="2" :value="$page?->meta_description" maxlength="160" />
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Publishing">
            <div class="space-y-4">
                <fieldset>
                    <legend class="text-sm font-medium text-slate-700">Status</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach(\App\Enums\ContentStatus::cases() as $option)
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-slate-300 has-checked:bg-brand-50 has-checked:text-brand-700 has-checked:ring-brand-500 has-disabled:cursor-not-allowed has-disabled:opacity-50">
                                <input type="radio" name="status" value="{{ $option->value }}" class="sr-only" @checked($status === $option->value)
                                       @disabled(! $canPublish && $option === \App\Enums\ContentStatus::Published && $status !== 'published')>
                                {{ $option->label() }}
                            </label>
                        @endforeach
                    </div>
                    @error('status')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                    @unless($canPublish)<p class="mt-1.5 text-xs text-slate-500">Publishing needs the Website content › Manage permission.</p>@endunless
                </fieldset>
                <x-admin.input name="published_at" type="datetime-local" label="Publish on" :value="$page?->published_at?->format('Y-m-d\TH:i')" hint="Leave empty to publish immediately. A future date schedules the page." />
                <x-admin.input name="unpublish_at" type="datetime-local" label="Unpublish on (optional)" :value="$page?->unpublish_at?->format('Y-m-d\TH:i')" />
            </div>
        </x-admin.card>

        <x-admin.card title="Page address">
            <label for="slug" class="block text-sm font-medium text-slate-700">Web address</label>
            <div class="mt-1.5 flex rounded-lg shadow-sm ring-1 ring-slate-300 focus-within:ring-2 focus-within:ring-brand-500">
                <span class="flex items-center pl-3 text-sm text-slate-400">/pages/</span>
                <input id="slug" name="slug" value="{{ old('slug', $page?->slug) }}" placeholder="about-us" class="block w-full rounded-r-lg border-0 bg-transparent py-2.5 pr-3 text-sm text-slate-900 focus:outline-none">
            </div>
            <p class="mt-1.5 text-xs text-slate-500">Created from the English title when empty.</p>
            @error('slug')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @if($isEditing && $page->isLive())
                <a href="{{ $page->publicUrl() }}" target="_blank" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:text-brand-800">View live page &nearr;</a>
            @endif
        </x-admin.card>

        <div class="flex flex-col gap-3">
            <x-admin.button form="page-form" class="w-full py-3">{{ $isEditing ? 'Save page' : 'Create page' }}</x-admin.button>
        </div>
    </div>
</form>

@if($isEditing)
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
        <form method="POST" action="{{ route('admin.cms.pages.destroy', $page) }}" class="mt-6 xl:max-w-[calc(100%-21.5rem)]" onsubmit="return confirm('Delete this page? Menu links to it will stop working.')">
            @csrf @method('DELETE')
            <button type="submit" class="cursor-pointer text-sm font-semibold text-red-600 hover:text-red-800">Delete page</button>
        </form>
    @endcan
@endif
@endsection
