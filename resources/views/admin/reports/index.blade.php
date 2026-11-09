@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Content reports</h1>
    <p class="mt-1 text-sm text-slate-500">Content users have flagged as breaking the platform rules.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\ReportStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.reports.index',
    ])
</div>

<form method="GET" action="{{ route('admin.reports.index') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end xl:max-w-3xl">
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

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Reported content</th>
                    <th class="px-6 py-3">Reason</th>
                    <th class="px-6 py-3">Reported by</th>
                    <th class="px-6 py-3">When</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reports as $report)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.reports.show', $report) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $report->title }}</a>
                            <p class="text-xs text-slate-500">#{{ $report->id }} · {{ $report->module?->label() ?? class_basename($report->reportable_type) }}</p>
                        </td>
                        <td class="px-6 py-4"><x-admin.badge color="red">{{ $report->reason->label() }}</x-admin.badge></td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $report->reporter?->name ?? 'Anonymous' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $report->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4"><x-admin.badge :color="$report->status->color()">{{ $report->status->label() }}</x-admin.badge></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <svg class="mx-auto size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg>
                            <p class="mt-3 font-medium text-slate-700">{{ $activeStatus === 'open' ? 'No open reports' : 'Nothing here' }}</p>
                            <p class="mt-1 text-slate-500">{{ $activeStatus === 'open' ? 'Reported content will appear here for review.' : 'No reports match these filters.' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
