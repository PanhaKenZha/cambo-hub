@extends('layouts.admin')
@section('title', 'Menus')
@section('content')
@php($canEdit = auth()->user()->can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Edit)))
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Menus</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $location->description() }}</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.menus.create', ['location' => $location->value])">Add menu item</x-admin.button>
    @endcan
</div>

<nav class="mt-6 flex gap-1 border-b border-slate-200" aria-label="Menu locations">
    @foreach(\App\Enums\MenuLocation::cases() as $tab)
        <a href="{{ route('admin.cms.menus.index', ['location' => $tab->value]) }}" @if($tab === $location) aria-current="page" @endif
           @class(['-mb-px border-b-2 px-4 py-2.5 text-sm font-medium', 'border-brand-600 text-brand-700' => $tab === $location, 'border-transparent text-slate-500 hover:text-slate-700' => $tab !== $location])>{{ $tab->label() }}</a>
    @endforeach
</nav>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <ul class="divide-y divide-slate-100">
        @forelse($items as $item)
            @include('admin.cms.menus._row', ['item' => $item, 'isChild' => false, 'isFirst' => $loop->first, 'isLast' => $loop->last])
            @foreach($item->children as $child)
                @include('admin.cms.menus._row', ['item' => $child, 'isChild' => true, 'isFirst' => $loop->first, 'isLast' => $loop->last])
            @endforeach
        @empty
            <li class="px-6 py-16 text-center text-sm text-slate-500">This menu is empty.</li>
        @endforelse
    </ul>
</div>
@endsection
