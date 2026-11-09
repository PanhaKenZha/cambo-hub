@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Content reports</h1>
    <p class="mt-1 text-sm text-ink-muted">Content users have flagged as breaking the platform rules.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\ReportStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.reports.index',
    ])
</div>

<form method="GET" action="{{ route('admin.reports.index') }}" role="search" aria-label="Filter reports" data-loading
      class="mt-4 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end xl:max-w-3xl">
    <input type="hidden" name="status" value="{{ $activeStatus }}">
    <x-admin.select name="reason" label="Reason">
        <option value="">Any reason</option>
        @foreach(\App\Enums\ReportReason::cases() as $reason)
            <option value="{{ $reason->value }}" @selected(($filters['reason'] ?? '') === $reason->value)>{{ $reason->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="module" label="Module">
        <option value="">All modules</option>
        @foreach($modules as $module)
            <option value="{{ $module->value }}" @selected(($filters['module'] ?? '') === $module->value)>{{ $module->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($reports->isEmpty())
    <x-ui.empty-state class="mt-4" icon="shield"
        :title="$activeStatus === 'open' ? 'No open reports' : 'Nothing here'"
        :description="$activeStatus === 'open' ? 'Reported content will appear here for review.' : 'No reports match these filters.'" />
@else
    <x-ui.table label="Content reports" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Reported content</th>
                <th scope="col">Reason</th>
                <th scope="col">Reported by</th>
                <th scope="col">When</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>
        @foreach($reports as $report)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Reported content">
                    <div class="min-w-0">
                        <a href="{{ route('admin.reports.show', $report) }}" class="rounded font-medium text-ink hover:text-primary">{{ $report->title }}</a>
                        <p class="text-xs text-ink-muted">#{{ $report->id }} · {{ $report->module?->label() ?? class_basename($report->reportable_type) }}</p>
                    </div>
                </td>
                <td data-label="Reason"><x-admin.badge color="red">{{ $report->reason->label() }}</x-admin.badge></td>
                <td data-label="Reported by" class="whitespace-nowrap text-ink">{{ $report->reporter?->name ?? 'Anonymous' }}</td>
                <td data-label="When" class="whitespace-nowrap">{{ $report->created_at->diffForHumans() }}</td>
                <td data-label="Status"><x-admin.badge :color="$report->status->color()">{{ $report->status->label() }}</x-admin.badge></td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
@endif
@endsection
