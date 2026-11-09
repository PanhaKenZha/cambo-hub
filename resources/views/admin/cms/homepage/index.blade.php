@extends('layouts.admin')
@section('title', 'Homepage')
@section('content')
@php
    use App\Enums\HomepageSectionType;
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $canEdit = auth()->user()->can(PermissionArea::WebsiteContent->permission(PermissionAction::Edit));
    $shownCount = $sections->filter(fn ($section) => $section->isShown())->count();
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Homepage</h1>
        <p class="mt-1 text-sm text-slate-500">The sections of the public homepage, top to bottom. {{ $shownCount }} of {{ $sections->count() }} are currently shown — sections for a turned-off module are hidden automatically.</p>
    </div>
    @can(PermissionArea::WebsiteContent->permission(PermissionAction::Create))
        <details class="relative">
            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                Add section
                <svg class="size-4 opacity-80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
            </summary>
            <div class="absolute right-0 z-20 mt-2 w-64 overflow-hidden rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-slate-200">
                @foreach(HomepageSectionType::cases() as $type)
                    <a href="{{ route('admin.cms.homepage.create', ['type' => $type->value]) }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ $type->label() }}</a>
                @endforeach
            </div>
        </details>
    @endcan
</div>

<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="w-16 px-6 py-3 text-center">#</th>
                    <th class="px-6 py-3">Section</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Content</th>
                    <th class="px-6 py-3">Status</th>
                    @if($canEdit)<th class="px-6 py-3 text-center">Order</th>@endif
                    <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sections as $section)
                    @php
                        $module = $section->type->module();
                        $moduleOff = $module !== null && ! $module->isEnabled();
                    @endphp
                    <tr @class(['hover:bg-slate-50/70', 'bg-slate-50/40' => ! $section->isShown()])>
                        <td class="px-6 py-3.5 text-center">
                            <span @class(['inline-flex size-7 items-center justify-center rounded-full text-xs font-semibold', 'bg-brand-50 text-brand-700' => $section->isShown(), 'bg-slate-100 text-slate-400' => ! $section->isShown()])>{{ $loop->iteration }}</span>
                        </td>
                        <td class="max-w-md px-6 py-3.5">
                            <a href="{{ route('admin.cms.homepage.edit', $section) }}" @class(['font-medium hover:text-brand-700', 'text-slate-900' => $section->isShown(), 'text-slate-500' => ! $section->isShown()])>{{ $section->translate('title', 'en') ?: $section->type->label() }}</a>
                            @if($section->translate('subtitle', 'en'))<p class="truncate text-xs text-slate-500">{{ $section->translate('subtitle', 'en') }}</p>@endif
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap"><x-admin.badge color="gray">{{ $section->type->label() }}</x-admin.badge></td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">
                            @if($section->type->showsItems())
                                {{ $section->item_count }} {{ $module->label() }} items
                            @elseif($section->link_url)
                                <span class="font-mono text-xs">{{ $section->link_url }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if(! $section->is_visible)
                                <x-admin.badge color="gray">Hidden</x-admin.badge>
                            @elseif($moduleOff)
                                <x-admin.badge color="amber">Module off</x-admin.badge>
                                <p class="mt-1 text-xs text-slate-500">{{ $module->label() }} is turned off</p>
                            @else
                                <x-admin.badge color="green">Shown</x-admin.badge>
                            @endif
                        </td>
                        @if($canEdit)
                            <td class="px-6 py-3.5">
                                <div class="flex justify-center gap-1">
                                    @foreach(['up' => '&uarr;', 'down' => '&darr;'] as $direction => $arrow)
                                        <form method="POST" action="{{ route('admin.cms.homepage.move', $section) }}">
                                            @csrf <input type="hidden" name="direction" value="{{ $direction }}">
                                            <button type="submit" class="size-8 cursor-pointer rounded-md text-slate-500 hover:bg-slate-100 disabled:cursor-default disabled:opacity-30"
                                                    @disabled($direction === 'up' ? $loop->parent->first : $loop->parent->last) aria-label="Move {{ $direction }}">{!! $arrow !!}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                        @endif
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            @if($canEdit)
                                <form method="POST" action="{{ route('admin.cms.homepage.toggle', $section) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="cursor-pointer text-sm font-semibold text-slate-500 hover:text-slate-800">{{ $section->is_visible ? 'Hide' : 'Show' }}</button>
                                </form>
                                <a href="{{ route('admin.cms.homepage.edit', $section) }}" class="ml-4 text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 7 : 6 }}" class="px-6 py-16 text-center">
                            <p class="font-medium text-slate-700">The homepage has no sections yet</p>
                            <p class="mt-1 text-slate-500">Use “Add section” to build the homepage.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
