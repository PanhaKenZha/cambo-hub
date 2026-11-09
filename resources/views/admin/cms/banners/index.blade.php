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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Banners</h1>
        <p class="mt-1 text-sm text-slate-500">Image banners for each place on the website. Banners can start and end on set dates.</p>
    </div>
    @can(PermissionArea::WebsiteContent->permission(PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.banners.create', array_filter(['placement' => $activePlacement?->value]))">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Add banner
        </x-admin.button>
    @endcan
</div>

{{-- Placement filter --}}
<nav class="mt-6 flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="Filter by placement">
    @foreach([null, ...BannerPlacement::cases()] as $tab)
        @php($isActive = $tab === $activePlacement)
        <a href="{{ route('admin.cms.banners.index', array_filter(['placement' => $tab?->value])) }}" @if($isActive) aria-current="page" @endif
           @class([
               '-mb-px flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap',
               'border-brand-600 text-brand-700' => $isActive,
               'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => ! $isActive,
           ])>
            {{ $tab?->label() ?? 'All placements' }}
            <span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-brand-100 text-brand-700' => $isActive, 'bg-slate-100 text-slate-600' => ! $isActive])>{{ $tab ? ($placementCounts[$tab->value] ?? 0) : $placementCounts->sum() }}</span>
        </a>
    @endforeach
</nav>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Banner</th>
                    <th class="px-6 py-3">Placement</th>
                    <th class="px-6 py-3">Schedule</th>
                    <th class="px-6 py-3">Status</th>
                    @if($canEdit)<th class="px-6 py-3 text-center">Order</th>@endif
                    <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($banners as $banner)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-28 shrink-0 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200">
                                    @if($banner->imageUrl())<img src="{{ $banner->imageUrl() }}" alt="" class="size-full object-cover">@endif
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $banner->translate('title', 'en') }}</a>
                                    @if($banner->link_url)<p class="truncate font-mono text-xs text-slate-500">{{ $banner->link_url }}</p>@endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <span class="text-slate-700">{{ $banner->placement->label() }}</span>
                            <p class="text-xs text-slate-400">{{ $banner->placement->recommendedSize() }}</p>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-slate-600">
                            {{ $banner->starts_at ? $banner->starts_at->toFormattedDateString() : 'Always' }}
                            @if($banner->ends_at)<span class="text-slate-400">→</span> {{ $banner->ends_at->toFormattedDateString() }}@endif
                        </td>
                        <td class="px-6 py-3">
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
                            <td class="px-6 py-3">
                                <div class="flex justify-center gap-1">
                                    @foreach(['up' => '&uarr;', 'down' => '&darr;'] as $direction => $arrow)
                                        <form method="POST" action="{{ route('admin.cms.banners.move', $banner) }}">
                                            @csrf <input type="hidden" name="direction" value="{{ $direction }}">
                                            <button type="submit" class="size-8 cursor-pointer rounded-md text-slate-500 hover:bg-slate-100 disabled:cursor-default disabled:opacity-30"
                                                    @disabled($edges[$banner->placement->value][$direction === 'up' ? 'first' : 'last'] === $banner->id)
                                                    aria-label="Move {{ $direction }}">{!! $arrow !!}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                        @endif
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            @if($canEdit)
                                <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 6 : 5 }}" class="px-6 py-16 text-center">
                            <svg class="mx-auto size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            <p class="mt-3 font-medium text-slate-700">No banners yet</p>
                            <p class="mt-1 text-slate-500">Add a banner to show it {{ $activePlacement ? 'in the '.mb_strtolower($activePlacement->label()) : 'on the website' }}.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
