@extends('layouts.admin')
@section('title', 'Banners')
@section('content')
@php
    use App\Enums\BannerPlacement;
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $canEdit = auth()->user()->can(PermissionArea::WebsiteContent->permission(PermissionAction::Edit));
    // First and last banner of each placement, to disable the move arrows at the ends.
    $edges = $banners->groupBy(fn ($banner) => $banner->placement->value)
        ->map(fn ($group) => ['first' => $group->first()->id, 'last' => $group->last()->id]);
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Banners</h1>
        <p class="mt-1 text-sm text-ink-muted">Image banners for each place on the website. Banners can start and end on set dates.</p>
    </div>
    @can(PermissionArea::WebsiteContent->permission(PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.banners.create', array_filter(['placement' => $activePlacement?->value]))">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Add banner
        </x-admin.button>
    @endcan
</div>

{{-- Placement filter --}}
<nav class="mt-6 flex gap-1 overflow-x-auto border-b border-line" aria-label="Filter by placement">
    @foreach([null, ...BannerPlacement::cases()] as $tab)
        @php($isActive = $tab === $activePlacement)
        <a href="{{ route('admin.cms.banners.index', array_filter(['placement' => $tab?->value])) }}" @if($isActive) aria-current="page" @endif
           @class([
               '-mb-px flex shrink-0 items-center gap-2 rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition focus-visible:-outline-offset-3',
               'border-primary text-primary' => $isActive,
               'border-transparent text-ink-muted hover:border-line-strong hover:text-ink' => ! $isActive,
           ])>
            {{ $tab?->label() ?? 'All placements' }}
            <span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-primary-soft text-primary' => $isActive, 'bg-surface-muted text-ink-muted' => ! $isActive])>{{ $tab ? ($placementCounts[$tab->value] ?? 0) : $placementCounts->sum() }}</span>
        </a>
    @endforeach
</nav>

@if($banners->isEmpty())
    <x-ui.empty-state class="mt-4" icon="advertising" title="No banners yet" description="Add a banner to show it {{ $activePlacement ? 'in the '.mb_strtolower($activePlacement->label()) : 'on the website' }}." />
@else
    <x-ui.table label="Banners" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Banner</th>
                <th scope="col">Placement</th>
                <th scope="col">Schedule</th>
                <th scope="col">Status</th>
                @if($canEdit)<th scope="col" class="text-center">Order</th>@endif
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($banners as $banner)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Banner">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="h-12 w-28 shrink-0 overflow-hidden rounded-lg bg-surface-muted ring-1 ring-line">
                            @if($banner->imageUrl())<img src="{{ $banner->imageUrl() }}" alt="" class="size-full object-cover">@endif
                        </div>
                        <div class="min-w-0 text-left">
                            <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="rounded font-medium text-ink hover:text-primary">{{ $banner->translate('title', 'en') }}</a>
                            @if($banner->link_url)<p class="truncate font-mono text-xs text-ink-muted">{{ $banner->link_url }}</p>@endif
                        </div>
                    </div>
                </td>
                <td data-label="Placement" class="whitespace-nowrap">
                    <div>
                        <span class="text-ink">{{ $banner->placement->label() }}</span>
                        <p class="text-xs text-ink-subtle">{{ $banner->placement->recommendedSize() }}</p>
                    </div>
                </td>
                <td data-label="Schedule" class="whitespace-nowrap">
                    <div>
                        {{ $banner->starts_at ? $banner->starts_at->toFormattedDateString() : 'Always' }}
                        @if($banner->ends_at)<span class="text-ink-subtle">→</span> {{ $banner->ends_at->toFormattedDateString() }}@endif
                    </div>
                </td>
                <td data-label="Status">
                    @if($banner->isLive())
                        <x-admin.badge color="green">Live</x-admin.badge>
                    @elseif(! $banner->is_active)
                        <x-admin.badge color="gray">Off</x-admin.badge>
                    @elseif($banner->starts_at?->isFuture())
                        <x-admin.badge color="violet">Scheduled</x-admin.badge>
                    @else
                        <x-admin.badge color="gray">Ended</x-admin.badge>
                    @endif
                </td>
                @if($canEdit)
                    <td data-label="Order">
                        <div class="flex justify-center gap-1">
                            @foreach(['up' => '&uarr;', 'down' => '&darr;'] as $direction => $arrow)
                                <form method="POST" action="{{ route('admin.cms.banners.move', $banner) }}">
                                    @csrf <input type="hidden" name="direction" value="{{ $direction }}">
                                    <button type="submit" class="size-8 cursor-pointer rounded-md text-ink-muted hover:bg-surface-muted hover:text-primary disabled:cursor-default disabled:opacity-30 disabled:hover:bg-transparent"
                                            @disabled($edges[$banner->placement->value][$direction === 'up' ? 'first' : 'last'] === $banner->id)
                                            aria-label="Move {{ $direction }}">{!! $arrow !!}</button>
                                </form>
                            @endforeach
                        </div>
                    </td>
                @endif
                <td data-label="Actions" class="whitespace-nowrap sm:text-right">
                    <div>
                        @if($canEdit)
                            <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif
@endsection
