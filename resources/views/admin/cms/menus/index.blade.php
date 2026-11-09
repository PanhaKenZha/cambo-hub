@extends('layouts.admin')
@section('title', 'Menus')
@section('content')
@php($canEdit = auth()->user()->can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Edit)))
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Menus</h1>
        <p class="mt-1 text-sm text-ink-muted">{{ $location->description() }}</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.menus.create', ['location' => $location->value])">Add menu item</x-admin.button>
    @endcan
</div>

<nav class="mt-6 flex gap-1 overflow-x-auto border-b border-line" aria-label="Menu locations">
    @foreach(\App\Enums\MenuLocation::cases() as $tab)
        <a href="{{ route('admin.cms.menus.index', ['location' => $tab->value]) }}" @if($tab === $location) aria-current="page" @endif
           @class(['-mb-px shrink-0 rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition focus-visible:-outline-offset-3', 'border-primary text-primary' => $tab === $location, 'border-transparent text-ink-muted hover:border-line-strong hover:text-ink' => $tab !== $location])>{{ $tab->label() }}</a>
    @endforeach
</nav>

@if($items->isEmpty())
    <x-ui.empty-state class="mt-4" icon="menu" title="This menu is empty." />
@else
    <div class="mt-4 overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line">
        <ul class="divide-y divide-line" aria-label="{{ $location->label() }}">
            @foreach($items as $item)
                @include('admin.cms.menus._row', ['item' => $item, 'isChild' => false, 'isFirst' => $loop->first, 'isLast' => $loop->last])
                @foreach($item->children as $child)
                    @include('admin.cms.menus._row', ['item' => $child, 'isChild' => true, 'isFirst' => $loop->first, 'isLast' => $loop->last])
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
@endsection
