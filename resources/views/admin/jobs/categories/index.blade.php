@extends('layouts.admin')
@section('title', 'Job categories')
@section('content')
@include('admin.jobs._nav')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Categories</h1>
        <p class="mt-1 text-sm text-ink-muted">Job fields such as Sales or IT. The category is shown as the label on homepage job cards.</p>
    </div>
    @can(\App\Enums\PermissionArea::Jobs->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.jobs.categories.create')">New category</x-admin.button>
    @endcan
</div>

@if($categories->isEmpty())
    <x-ui.empty-state class="mt-6" title="No categories yet." />
@else
    <x-ui.table label="Job categories" class="mt-6">
        <x-slot:head>
            <tr>
                <th scope="col">Category</th>
                <th scope="col">Address</th>
                <th scope="col" class="text-right">Jobs</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($categories as $category)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Category">
                    <span class="font-medium text-ink">{{ $category->translate('name', 'en') }}</span>
                    @if(filled($category->name['km'] ?? null))<span class="ml-1 text-ink-muted">· {{ $category->name['km'] }}</span>@endif
                </td>
                <td data-label="Address" class="font-mono text-xs">{{ $category->slug }}</td>
                <td data-label="Jobs" class="text-right tabular-nums">
                    @if($category->job_listings_count > 0)
                        <a href="{{ route('admin.jobs.listings.index', ['category' => $category->id]) }}" class="text-primary hover:underline">{{ $category->job_listings_count }}</a>
                    @else
                        0
                    @endif
                </td>
                <td data-label="Status">@if($category->is_active)<x-admin.badge color="green">Active</x-admin.badge>@else<x-admin.badge color="gray">Hidden</x-admin.badge>@endif</td>
                <td class="text-right whitespace-nowrap">
                    @can(\App\Enums\PermissionArea::Jobs->permission(\App\Enums\PermissionAction::Edit))
                        <a href="{{ route('admin.jobs.categories.edit', $category) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif
@endsection
