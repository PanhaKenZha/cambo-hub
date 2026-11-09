@extends('layouts.admin')
@php($isEditing = $banner->exists)
@section('title', $isEditing ? 'Edit banner' : 'Add banner')
@section('content')
<a href="{{ route('admin.cms.banners.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Banners</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? 'Edit banner' : 'Add banner' }}</h1>

<form method="POST" action="{{ $isEditing ? route('admin.cms.banners.update', $banner) : route('admin.cms.banners.store') }}" enctype="multipart/form-data" class="mt-6 grid max-w-6xl gap-6 xl:grid-cols-[1fr_20rem]" data-loading>
    <x-ui.error-summary class="xl:col-span-2" :ids="collect($errors->keys())->mapWithKeys(fn ($key) => [$key => str_replace('.', '_', $key)])->all()" />
    @csrf
    @if($isEditing) @method('PUT') @endif

    <div class="min-w-0 space-y-6">
        <x-admin.card title="Banner">
            <div class="space-y-5">
                <x-admin.translatable-field name="title" label="Headline" :value="$banner->title" maxlength="150" required />
                <x-admin.translatable-field name="subtitle" label="Supporting text" type="textarea" rows="2" :value="$banner->subtitle" maxlength="300" />
                <x-admin.translatable-field name="button_label" label="Button text" :value="$banner->button_label" maxlength="40" placeholder="e.g. Learn more" />
                <div class="max-w-xl">
                    <x-admin.input name="link_url" label="Link" :value="$banner->link_url" placeholder="/jobs or https://example.com" />
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Image">
            @if($banner->imageUrl())
                <img src="{{ $banner->imageUrl() }}" alt="" class="mb-4 aspect-[3/1] w-full rounded-lg object-cover ring-1 ring-line">
            @endif
            <label for="image" class="block text-sm font-medium text-ink">{{ $banner->imageUrl() ? 'Replace image' : 'Upload image' }}</label>
            <input id="image" name="image" type="file" accept="image/*" @if(! $isEditing) required @endif
                   aria-describedby="image-hint @error('image') image-error @enderror" @error('image') aria-invalid="true" @enderror
                   class="mt-1.5 block w-full rounded-lg text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary-soft/70 aria-invalid:text-danger">
            <p id="image-hint" class="mt-1.5 text-xs text-ink-subtle">Size limit and file types follow Settings › Uploads.</p>
            @error('image')<p id="image-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
        </x-admin.card>

        <x-admin.card title="Placement & schedule">
            <div class="space-y-4">
                <x-admin.select name="placement" label="Show in">
                    @foreach(\App\Enums\BannerPlacement::cases() as $placement)
                        <option value="{{ $placement->value }}" @selected(old('placement', $banner->placement->value) === $placement->value)>{{ $placement->label() }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.input name="starts_at" type="datetime-local" label="Start (optional)" :value="$banner->starts_at?->format('Y-m-d\TH:i')" />
                <x-admin.input name="ends_at" type="datetime-local" label="End (optional)" :value="$banner->ends_at?->format('Y-m-d\TH:i')" />
                <label class="flex items-center gap-2.5 text-sm text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="size-4 cursor-pointer rounded accent-brand-600 dark:accent-brand-300" @checked(old('is_active', $banner->is_active))> Active
                </label>
            </div>
        </x-admin.card>

        <x-admin.button class="w-full py-3" loading-text="Saving…">{{ $isEditing ? 'Save banner' : 'Add banner' }}</x-admin.button>
        @if($isEditing)
            @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
                <button type="submit" form="delete-form" class="w-full cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete banner</button>
            @endcan
        @endif
    </div>
</form>
@if($isEditing)
    <form method="POST" action="{{ route('admin.cms.banners.destroy', $banner) }}" id="delete-form" onsubmit="return confirm('Delete this banner?')">@csrf @method('DELETE')</form>
@endif
@endsection
