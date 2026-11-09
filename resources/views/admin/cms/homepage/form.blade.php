@extends('layouts.admin')
@php($isEditing = $section->exists)
@section('title', ($isEditing ? 'Edit ' : 'Add ').$section->type->label())
@section('content')
<a href="{{ route('admin.cms.homepage.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Homepage</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? 'Edit section' : 'Add section' }}: {{ $section->type->label() }}</h1>
@if($section->type->module())
    <p class="mt-1 text-sm text-ink-muted">Shows content from the {{ $section->type->module()->label() }} module, and is hidden while that module is turned off.</p>
@endif
@if($section->type === \App\Enums\HomepageSectionType::PromotionalBanners)
    <p class="mt-1 text-sm text-ink-muted">Shows the active banners with the “{{ \App\Enums\BannerPlacement::HomepageHero->label() }}” placement. Add or schedule them in <a href="{{ route('admin.cms.banners.index') }}" class="rounded font-semibold text-primary hover:underline">Website › Banners</a>.</p>
@endif

<form method="POST" action="{{ $isEditing ? route('admin.cms.homepage.update', $section) : route('admin.cms.homepage.store') }}" enctype="multipart/form-data" class="mt-6 max-w-4xl space-y-6" data-loading>
    <x-ui.error-summary :ids="collect($errors->keys())->mapWithKeys(fn ($key) => [$key => str_replace('.', '_', $key)])->all()" />
    @csrf
    @if($isEditing) @method('PUT') @else <input type="hidden" name="type" value="{{ $section->type->value }}"> @endif

    <x-admin.card title="Text">
        <div class="space-y-5">
            <x-admin.translatable-field name="title" label="Heading" :value="$section->title" maxlength="150" :placeholder="$section->type->label()" />
            <x-admin.translatable-field name="subtitle" label="Description" type="textarea" rows="2" :value="$section->subtitle" maxlength="300" />
            @if($section->type->hasCallToAction() || $section->type === \App\Enums\HomepageSectionType::Hero)
                <x-admin.translatable-field name="button_label" label="Button text" :value="$section->button_label" maxlength="40" />
                <div class="max-w-xl"><x-admin.input name="link_url" label="Button link" :value="$section->link_url" placeholder="/jobs or https://example.com" /></div>
            @elseif($section->type->hasLink())
                <x-admin.translatable-field name="button_label" label="“View all” link text" :value="$section->button_label" maxlength="40" placeholder="View all" />
                <div class="max-w-xl"><x-admin.input name="link_url" label="“View all” link" :value="$section->link_url" placeholder="/search?type=news" hint="Leave empty to hide the link." /></div>
            @endif
        </div>
    </x-admin.card>

    @if($section->type->showsItems())
        <x-admin.card title="Items">
            <div class="max-w-xs"><x-admin.input name="item_count" type="number" min="1" max="24" label="Number of items to show" :value="$section->item_count" /></div>
        </x-admin.card>
    @endif

    @if($section->type->hasCallToAction() || $section->type === \App\Enums\HomepageSectionType::Hero)
        <x-admin.card title="Image">
            @if($section->imageUrl())<img src="{{ $section->imageUrl() }}" alt="" class="mb-4 max-h-48 rounded-lg object-cover ring-1 ring-line">@endif
            <label for="image" class="sr-only">Image</label>
            <input id="image" name="image" type="file" accept="image/*" @error('image') aria-invalid="true" aria-describedby="image-error" @enderror
                   class="block w-full rounded-lg text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary-soft/70 aria-invalid:text-danger">
            @error('image')<p id="image-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
        </x-admin.card>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <label class="flex items-center gap-2.5 text-sm text-ink">
            <input type="hidden" name="is_visible" value="0">
            <input type="checkbox" id="is_visible" name="is_visible" value="1" class="size-4 cursor-pointer rounded accent-brand-600 dark:accent-brand-300" @checked(old('is_visible', $section->is_visible))> Show on the homepage
        </label>
        <div class="flex flex-wrap items-center gap-3">
            @if($isEditing)
                @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
                    <button type="submit" form="delete-form" class="cursor-pointer rounded px-3 text-sm font-semibold text-danger hover:underline">Remove</button>
                @endcan
            @endif
            <x-admin.button variant="secondary" :href="route('admin.cms.homepage.index')">Cancel</x-admin.button>
            <x-admin.button loading-text="Saving…">{{ $isEditing ? 'Save section' : 'Add section' }}</x-admin.button>
        </div>
    </div>
</form>
@if($isEditing)
    <form method="POST" action="{{ route('admin.cms.homepage.destroy', $section) }}" id="delete-form" onsubmit="return confirm('Remove this section from the homepage?')">@csrf @method('DELETE')</form>
@endif
@endsection
