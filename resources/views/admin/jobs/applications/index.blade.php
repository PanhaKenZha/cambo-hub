@extends('layouts.admin')
@section('title', 'Applications')
@section('content')
@include('admin.jobs._nav')

<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Applications</h1>
    <p class="mt-1 text-sm text-ink-muted">
        @if($filteredJob)
            Candidates for <span class="font-semibold text-ink">{{ $filteredJob->translate('title', 'en') }}</span> at {{ $filteredJob->company->name }}.
            <a href="{{ route('admin.jobs.applications.index', request()->except(['job', 'page'])) }}" class="font-medium text-primary hover:underline">Show all jobs</a>
        @else
            CVs sent through Cambo Hub. Jobs that use a company website or email are not listed here.
        @endif
    </p>
</div>

<nav class="mt-6 flex gap-1 overflow-x-auto" aria-label="Filter by status">
    @php($activeStatus = $filters['status'] ?? null)
    <a href="{{ route('admin.jobs.applications.index', request()->except(['status', 'page'])) }}" @if($activeStatus === null) aria-current="page" @endif
       @class(['flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap', 'bg-primary-soft text-primary ring-1 ring-primary/20' => $activeStatus === null, 'text-ink-muted hover:bg-surface-muted hover:text-ink' => $activeStatus !== null])>
        All <span class="text-xs tabular-nums opacity-70">{{ $statusCounts->sum() }}</span>
    </a>
    @foreach(\App\Enums\ApplicationStatus::cases() as $status)
        @php($isActive = $activeStatus === $status->value)
        <a href="{{ route('admin.jobs.applications.index', array_merge(request()->except(['status', 'page']), ['status' => $status->value])) }}" @if($isActive) aria-current="page" @endif
           @class(['flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap', 'bg-primary-soft text-primary ring-1 ring-primary/20' => $isActive, 'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive])>
            {{ $status->label() }} <span class="text-xs tabular-nums opacity-70">{{ $statusCounts[$status->value] ?? 0 }}</span>
        </a>
    @endforeach
</nav>

<form method="GET" action="{{ route('admin.jobs.applications.index') }}" role="search" aria-label="Search applications" data-loading
      class="mt-4 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-[1fr_auto] sm:items-end">
    @foreach(request()->only(['status', 'job']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Candidate name or email" />
    <x-admin.button variant="secondary">Search</x-admin.button>
</form>

@if($applications->isEmpty())
    <x-ui.empty-state class="mt-4" title="No applications here." />
@else
    <x-ui.table label="Applications" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Candidate</th>
                <th scope="col">Job</th>
                <th scope="col">Received</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($applications as $application)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Candidate">
                    <a href="{{ route('admin.jobs.applications.show', $application) }}" class="rounded font-medium text-ink hover:text-primary">{{ $application->name }}</a>
                    <p class="text-xs text-ink-subtle">{{ $application->email }}</p>
                </td>
                <td data-label="Job">
                    <div class="text-sm">{{ $application->jobListing->translate('title', 'en') }}</div>
                    <p class="text-xs text-ink-subtle">{{ $application->jobListing->company->name }}</p>
                </td>
                <td data-label="Received" class="text-sm whitespace-nowrap" title="{{ $application->created_at->format('j M Y H:i') }}">{{ $application->created_at->diffForHumans() }}</td>
                <td data-label="Status"><x-admin.badge :color="$application->status->color()">{{ $application->status->label() }}</x-admin.badge></td>
                <td class="text-right whitespace-nowrap">
                    @if($application->cv_path)
                        <a href="{{ route('admin.jobs.applications.cv', $application) }}" class="rounded text-sm font-semibold text-ink-muted hover:text-ink">CV</a>
                    @endif
                    <a href="{{ route('admin.jobs.applications.show', $application) }}" class="ml-3 rounded text-sm font-semibold text-primary hover:text-primary-hover">Open</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    @if($applications->hasPages())<div class="mt-4">{{ $applications->links() }}</div>@endif
@endif
@endsection
