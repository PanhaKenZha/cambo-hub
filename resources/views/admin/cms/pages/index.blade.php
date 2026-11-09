@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Pages</h1>
        <p class="mt-1 text-sm text-ink-muted">Custom pages such as About us, Terms and Privacy, in English and Khmer.</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.pages.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            New page
        </x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.cms.pages.index') }}" role="search" aria-label="Filter pages" data-loading
      class="mt-6 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-[2fr_1fr_auto] sm:items-end xl:max-w-3xl">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Title or address" />
    <x-admin.select name="status" label="Status">
        <option value="">Any status</option>
        @foreach(\App\Enums\ContentStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($pages->isEmpty())
    <x-ui.empty-state class="mt-4" title="No pages yet." />
@else
    <x-ui.table label="Pages" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Page</th>
                <th scope="col">Languages</th>
                <th scope="col">Status</th>
                <th scope="col">Last edited</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($pages as $page)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Page">
                    <div class="min-w-0">
                        <a href="{{ route('admin.cms.pages.edit', $page) }}" class="rounded font-medium text-ink hover:text-primary">{{ $page->translate('title', 'en') }}</a>
                        <p class="font-mono text-xs break-all text-ink-muted">/pages/{{ $page->slug }}</p>
                    </div>
                </td>
                <td data-label="Languages">
                    <div class="flex gap-1">
                        <x-admin.badge>EN</x-admin.badge>
                        @if(filled($page->title['km'] ?? null))<x-admin.badge>KM</x-admin.badge>@else<x-admin.badge color="gray">KM missing</x-admin.badge>@endif
                    </div>
                </td>
                <td data-label="Status">
                    <div>
                        <x-admin.badge :color="$page->stateColor()">{{ $page->stateLabel() }}</x-admin.badge>
                        @if($page->stateLabel() === 'Scheduled')<p class="mt-1 text-xs text-ink-muted">{{ $page->published_at->toDayDateTimeString() }}</p>@endif
                    </div>
                </td>
                <td data-label="Last edited" class="whitespace-nowrap">
                    <div>{{ $page->updated_at->diffForHumans() }}<br><span class="text-xs text-ink-subtle">{{ $page->editor?->name }}</span></div>
                </td>
                <td data-label="Actions" class="whitespace-nowrap sm:text-right">
                    <div>
                        <a href="{{ route('admin.cms.pages.preview', $page) }}" target="_blank" class="rounded text-sm font-semibold text-ink-muted hover:text-ink">Preview</a>
                        <a href="{{ route('admin.cms.pages.edit', $page) }}" class="ml-4 rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($pages->hasPages())
    <div class="mt-4">{{ $pages->links() }}</div>
@endif
@endsection
