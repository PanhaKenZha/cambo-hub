@extends('layouts.admin')
@php($isEditing = $section->exists)
@section('title', ($isEditing ? 'Edit ' : 'Add ').$section->type->label())
@section('content')
<a href="{{ route('admin.cms.homepage.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Homepage</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $isEditing ? 'Edit section' : 'Add section' }}: {{ $section->type->label() }}</h1>
@if($section->type->module())
    <p class="mt-1 text-sm text-slate-500">Shows content from the {{ $section->type->module()->label() }} module, and is hidden while that module is turned off.</p>
@endif

<form method="POST" action="{{ $isEditing ? route('admin.cms.homepage.update', $section) : route('admin.cms.homepage.store') }}" enctype="multipart/form-data" class="mt-6 max-w-4xl space-y-6">
    @csrf
    @if($isEditing) @method('PUT') @else <input type="hidden" name="type" value="{{ $section->type->value }}"> @endif

    <x-admin.card title="Text">
        <div class="space-y-5">
            <x-admin.translatable-field name="title" label="Heading" :value="$section->title" maxlength="150" :placeholder="$section->type->label()" />
            <x-admin.translatable-field name="subtitle" label="Description" type="textarea" rows="2" :value="$section->subtitle" maxlength="300" />
            @if($section->type->hasCallToAction() || $section->type === \App\Enums\HomepageSectionType::Hero)
                <x-admin.translatable-field name="button_label" label="Button text" :value="$section->button_label" maxlength="40" />
                <div class="max-w-xl"><x-admin.input name="link_url" label="Button link" :value="$section->link_url" placeholder="/jobs or https://example.com" /></div>
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
            @if($section->imageUrl())<img src="{{ $section->imageUrl() }}" alt="" class="mb-4 max-h-48 rounded-lg object-cover ring-1 ring-slate-200">@endif
            <input name="image" type="file" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-700">
            @error('image')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </x-admin.card>
    @endif

    <div class="flex items-center justify-between gap-3">
        <label class="flex items-center gap-2.5 text-sm text-slate-700">
            <input type="hidden" name="is_visible" value="0">
            <input type="checkbox" name="is_visible" value="1" class="size-4 rounded accent-brand-600" @checked(old('is_visible', $section->is_visible))> Show on the homepage
        </label>
        <div class="flex gap-3">
            @if($isEditing)
                @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
                    <button type="submit" form="delete-form" class="cursor-pointer px-3 text-sm font-semibold text-red-600 hover:text-red-800">Remove</button>
                @endcan
            @endif
            <x-admin.button variant="secondary" :href="route('admin.cms.homepage.index')">Cancel</x-admin.button>
            <x-admin.button>{{ $isEditing ? 'Save section' : 'Add section' }}</x-admin.button>
        </div>
    </div>
</form>
@if($isEditing)
    <form method="POST" action="{{ route('admin.cms.homepage.destroy', $section) }}" id="delete-form" onsubmit="return confirm('Remove this section from the homepage?')">@csrf @method('DELETE')</form>
@endif
@endsection
