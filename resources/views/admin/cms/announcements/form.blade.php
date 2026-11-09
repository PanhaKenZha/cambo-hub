@extends('layouts.admin')
@php($isEditing = $announcement->exists)
@section('title', $isEditing ? 'Edit announcement' : 'New announcement')
@section('content')
<a href="{{ route('admin.cms.announcements.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Announcements</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $isEditing ? 'Edit announcement' : 'New announcement' }}</h1>

<form method="POST" action="{{ $isEditing ? route('admin.cms.announcements.update', $announcement) : route('admin.cms.announcements.store') }}" class="mt-6 grid max-w-6xl gap-6 xl:grid-cols-[1fr_20rem]">
    @csrf
    @if($isEditing) @method('PUT') @endif

    <x-admin.card title="Message">
        <div class="space-y-5">
            <x-admin.translatable-field name="message" label="Message" type="textarea" rows="2" :value="$announcement->message" maxlength="300" required />
            <x-admin.translatable-field name="link_label" label="Link text (optional)" :value="$announcement->link_label" maxlength="40" placeholder="e.g. Read more" />
            <div class="max-w-xl"><x-admin.input name="link_url" label="Link (optional)" :value="$announcement->link_url" placeholder="/pages/holiday-hours" /></div>
        </div>
    </x-admin.card>

    <div class="space-y-6">
        <x-admin.card title="Display">
            <div class="space-y-4">
                <fieldset>
                    <legend class="text-sm font-medium text-slate-700">Style</legend>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach(\App\Enums\AnnouncementLevel::cases() as $level)
                            <label class="cursor-pointer rounded-lg px-2 py-2 text-center text-sm font-medium ring-1 ring-slate-300 has-checked:bg-brand-50 has-checked:text-brand-700 has-checked:ring-brand-500">
                                <input type="radio" name="level" value="{{ $level->value }}" class="sr-only" @checked(old('level', $announcement->level->value) === $level->value)> {{ $level->label() }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <x-admin.input name="starts_at" type="datetime-local" label="Start (optional)" :value="$announcement->starts_at?->format('Y-m-d\TH:i')" />
                <x-admin.input name="ends_at" type="datetime-local" label="End (optional)" :value="$announcement->ends_at?->format('Y-m-d\TH:i')" />
                <label class="flex items-center gap-2.5 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded accent-brand-600" @checked(old('is_active', $announcement->is_active))> Active
                </label>
                <label class="flex items-center gap-2.5 text-sm text-slate-700">
                    <input type="hidden" name="is_dismissible" value="0">
                    <input type="checkbox" name="is_dismissible" value="1" class="size-4 rounded accent-brand-600" @checked(old('is_dismissible', $announcement->is_dismissible))> Visitors can close it
                </label>
            </div>
        </x-admin.card>
        <x-admin.button class="w-full py-3">{{ $isEditing ? 'Save announcement' : 'Create announcement' }}</x-admin.button>
        @if($isEditing)
            @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Delete))
                <button type="submit" form="delete-form" class="w-full cursor-pointer text-sm font-semibold text-red-600 hover:text-red-800">Delete announcement</button>
            @endcan
        @endif
    </div>
</form>
@if($isEditing)
    <form method="POST" action="{{ route('admin.cms.announcements.destroy', $announcement) }}" id="delete-form" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')</form>
@endif
@endsection
