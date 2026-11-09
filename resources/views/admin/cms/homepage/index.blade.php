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
        <h1 class="text-2xl font-bold tracking-tight text-ink">Homepage</h1>
        <p class="mt-1 text-sm text-ink-muted">The sections of the public homepage, top to bottom. {{ $shownCount }} of {{ $sections->count() }} are currently shown — sections for a turned-off module are hidden automatically.</p>
    </div>
    @can(PermissionArea::WebsiteContent->permission(PermissionAction::Create))
        <details class="relative">
            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:focus-visible:outline-brand-300 [&::-webkit-details-marker]:hidden">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                Add section
                <svg class="size-4 opacity-80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
            </summary>
            <div class="absolute right-0 z-20 mt-2 w-64 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl bg-surface-raised p-1.5 shadow-lg ring-1 ring-line">
                @foreach(HomepageSectionType::cases() as $type)
                    <a href="{{ route('admin.cms.homepage.create', ['type' => $type->value]) }}" class="block rounded-lg px-3 py-2 text-sm text-ink hover:bg-surface-muted hover:text-primary focus-visible:-outline-offset-2">{{ $type->label() }}</a>
                @endforeach
            </div>
        </details>
    @endcan
</div>

@if($sections->isEmpty())
    <x-ui.empty-state class="mt-6" icon="home" title="The homepage has no sections yet" description="Use “Add section” to build the homepage." />
@else
    <x-ui.table label="Homepage sections" class="mt-6">
        <x-slot:head>
            <tr>
                <th scope="col" class="w-16 text-center">#</th>
                <th scope="col">Section</th>
                <th scope="col">Type</th>
                <th scope="col">Content</th>
                <th scope="col">Status</th>
                @if($canEdit)<th scope="col" class="text-center">Order</th>@endif
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($sections as $section)
            @php
                $module = $section->type->module();
                $moduleOff = $module !== null && ! $module->isEnabled();
            @endphp
            <tr @class(['hover:bg-surface-muted/60', 'bg-surface-muted/40' => ! $section->isShown()])>
                <td data-label="#" class="sm:text-center">
                    <span @class(['inline-flex size-7 items-center justify-center rounded-full text-xs font-semibold', 'bg-primary-soft text-primary' => $section->isShown(), 'bg-surface-muted text-ink-subtle' => ! $section->isShown()])>{{ $loop->iteration }}</span>
                </td>
                <td data-label="Section" class="sm:max-w-md">
                    <div class="min-w-0">
                        <a href="{{ route('admin.cms.homepage.edit', $section) }}" @class(['rounded font-medium hover:text-primary', 'text-ink' => $section->isShown(), 'text-ink-muted' => ! $section->isShown()])>{{ $section->translate('title', 'en') ?: $section->type->label() }}</a>
                        @if($section->translate('subtitle', 'en'))<p class="truncate text-xs text-ink-muted">{{ $section->translate('subtitle', 'en') }}</p>@endif
                    </div>
                </td>
                <td data-label="Type" class="whitespace-nowrap"><x-admin.badge color="gray">{{ $section->type->label() }}</x-admin.badge></td>
                <td data-label="Content" class="sm:whitespace-nowrap">
                    <div class="min-w-0">
                        @if($section->type->showsItems())
                            {{ $section->item_count }} {{ $module ? $module->label().' items' : ($section->type === HomepageSectionType::PromotionalBanners ? 'banners' : 'items') }}
                        @elseif($section->link_url)
                            <span class="font-mono text-xs break-all">{{ $section->link_url }}</span>
                        @else
                            <span class="text-ink-subtle">—</span>
                        @endif
                    </div>
                </td>
                <td data-label="Status" class="whitespace-nowrap">
                    <div>
                        @if(! $section->is_visible)
                            <x-admin.badge color="gray">Hidden</x-admin.badge>
                        @elseif($moduleOff)
                            <x-admin.badge color="amber">Module off</x-admin.badge>
                            <p class="mt-1 text-xs text-ink-muted">{{ $module->label() }} is turned off</p>
                        @else
                            <x-admin.badge color="green">Shown</x-admin.badge>
                        @endif
                    </div>
                </td>
                @if($canEdit)
                    <td data-label="Order">
                        <div class="flex justify-center gap-1">
                            @foreach(['up' => '&uarr;', 'down' => '&darr;'] as $direction => $arrow)
                                <form method="POST" action="{{ route('admin.cms.homepage.move', $section) }}">
                                    @csrf <input type="hidden" name="direction" value="{{ $direction }}">
                                    <button type="submit" class="size-8 cursor-pointer rounded-md text-ink-muted hover:bg-surface-muted hover:text-primary disabled:cursor-default disabled:opacity-30 disabled:hover:bg-transparent"
                                            @disabled($direction === 'up' ? $loop->parent->first : $loop->parent->last) aria-label="Move {{ $direction }}">{!! $arrow !!}</button>
                                </form>
                            @endforeach
                        </div>
                    </td>
                @endif
                <td data-label="Actions" class="whitespace-nowrap sm:text-right">
                    <div>
                        @if($canEdit)
                            <form method="POST" action="{{ route('admin.cms.homepage.toggle', $section) }}" class="inline">
                                @csrf
                                <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-ink-muted hover:text-ink">{{ $section->is_visible ? 'Hide' : 'Show' }}</button>
                            </form>
                            <a href="{{ route('admin.cms.homepage.edit', $section) }}" class="ml-4 rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif
@endsection
