@extends('layouts.admin')
@section('title', 'Jobs')
@section('content')
@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $canManage = auth()->user()->can(PermissionArea::Jobs->permission(PermissionAction::Manage));
@endphp
@include('admin.jobs._nav')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Job listings</h1>
        <p class="mt-1 text-sm text-ink-muted">Review, publish, feature and close jobs. Live jobs appear on the homepage and in search automatically.</p>
    </div>
    @can(PermissionArea::Jobs->permission(PermissionAction::Create))
        <x-admin.button :href="route('admin.jobs.listings.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Post a job
        </x-admin.button>
    @endcan
</div>

<nav class="mt-6 flex gap-1 overflow-x-auto" aria-label="Filter by status">
    @foreach($tabs as $key => $label)
        @php($isActive = $key === $activeTab)
        <a href="{{ route('admin.jobs.listings.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}" @if($isActive) aria-current="page" @endif
           @class(['flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap', 'bg-primary-soft text-primary ring-1 ring-primary/20' => $isActive, 'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive])>
            {{ $label }} <span class="text-xs tabular-nums opacity-70">{{ $tabCounts[$key] }}</span>
        </a>
    @endforeach
</nav>

<form method="GET" action="{{ route('admin.jobs.listings.index') }}" role="search" aria-label="Filter jobs" data-loading
      class="mt-4 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <input type="hidden" name="tab" value="{{ $activeTab }}">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Job title or address" />
    <x-admin.select name="company" label="Company">
        <option value="">All companies</option>
        @foreach($companies as $company)
            <option value="{{ $company->id }}" @selected((int) ($filters['company'] ?? 0) === $company->id)>{{ $company->name }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="category" label="Category">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((int) ($filters['category'] ?? 0) === $category->id)>{{ $category->translate('name', 'en') }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($jobs->isEmpty())
    <x-ui.empty-state class="mt-4" title="No jobs here." />
@else
    <x-ui.table label="Job listings" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Job</th>
                <th scope="col">Company</th>
                <th scope="col">Type · Salary</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-right">Applicants</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($jobs as $job)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Job">
                    <div class="min-w-0">
                        <a href="{{ route('admin.jobs.listings.edit', $job) }}" class="rounded font-medium text-ink hover:text-primary">
                            @if($job->isFeaturedNow())<span class="text-warning" title="Featured">★</span>@endif
                            {{ $job->translate('title', 'en') }}
                        </a>
                        <p class="text-xs text-ink-subtle">{{ $job->category?->translate('name', 'en') ?? 'Uncategorised' }} · {{ $job->locationLabel() }}</p>
                    </div>
                </td>
                <td data-label="Company" class="whitespace-nowrap">
                    {{ $job->company->name }}
                    @if($job->company->isVerified())<span class="text-success" title="Verified company">✓</span>@endif
                </td>
                <td data-label="Type · Salary">
                    <div class="text-sm">{{ $job->employment_type->label() }}</div>
                    <div class="text-xs text-ink-subtle">{{ $job->salaryLabel() }}</div>
                </td>
                <td data-label="Status">
                    <div>
                        <x-admin.badge :color="$job->stateColor()">{{ $job->stateLabel() }}</x-admin.badge>
                        @if($job->closes_at && $job->stateLabel() !== 'Closed')<p class="mt-1 text-xs whitespace-nowrap text-ink-subtle">Closes {{ $job->closes_at->format('j M Y') }}</p>@endif
                    </div>
                </td>
                <td data-label="Applicants" class="text-right tabular-nums">
                    @if($job->applications_count > 0)
                        <a href="{{ route('admin.jobs.applications.index', ['job' => $job->id]) }}" class="font-semibold text-primary hover:underline">{{ $job->applications_count }}</a>
                    @else
                        0
                    @endif
                </td>
                <td class="text-right whitespace-nowrap">
                    @if($canManage && $job->isLive())
                        <form method="POST" action="{{ route('admin.jobs.listings.feature', $job) }}" class="inline">
                            @csrf
                            <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-ink-muted hover:text-ink">{{ $job->isFeaturedNow() ? 'Unfeature' : 'Feature' }}</button>
                        </form>
                    @endif
                    @if($job->isLive())
                        <a href="{{ $job->publicUrl() }}" target="_blank" class="ml-3 rounded text-sm font-semibold text-ink-muted hover:text-ink">View</a>
                    @endif
                    @can(PermissionArea::Jobs->permission(PermissionAction::Edit))
                        <a href="{{ route('admin.jobs.listings.edit', $job) }}" class="ml-3 rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    @if($jobs->hasPages())<div class="mt-4">{{ $jobs->links() }}</div>@endif
@endif
@endsection
