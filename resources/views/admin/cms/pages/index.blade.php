@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pages</h1>
        <p class="mt-1 text-sm text-slate-500">Custom pages such as About us, Terms and Privacy, in English and Khmer.</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.pages.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            New page
        </x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.cms.pages.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[2fr_1fr_auto] sm:items-end xl:max-w-3xl">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Title or address" />
    <x-admin.select name="status" label="Status">
        <option value="">Any status</option>
        @foreach(\App\Enums\ContentStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Page</th>
                    <th class="px-6 py-3">Languages</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Last edited</th>
                    <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pages as $page)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.cms.pages.edit', $page) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $page->translate('title', 'en') }}</a>
                            <p class="font-mono text-xs text-slate-500">/pages/{{ $page->slug }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-1">
                                <x-admin.badge>EN</x-admin.badge>
                                @if(filled($page->title['km'] ?? null))<x-admin.badge>KM</x-admin.badge>@else<x-admin.badge color="gray">KM missing</x-admin.badge>@endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <x-admin.badge :color="$page->stateColor()">{{ $page->stateLabel() }}</x-admin.badge>
                            @if($page->stateLabel() === 'Scheduled')<p class="mt-1 text-xs text-slate-500">{{ $page->published_at->toDayDateTimeString() }}</p>@endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $page->updated_at->diffForHumans() }}<br><span class="text-xs">{{ $page->editor?->name }}</span></td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.cms.pages.preview', $page) }}" target="_blank" class="text-sm font-semibold text-slate-500 hover:text-slate-800">Preview</a>
                            <a href="{{ route('admin.cms.pages.edit', $page) }}" class="ml-4 text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-16 text-center text-slate-500">No pages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pages->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $pages->links() }}</div>
    @endif
</div>
@endsection
