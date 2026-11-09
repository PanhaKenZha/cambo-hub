@extends('layouts.admin')
@section('title', 'Audit logs')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Audit logs</h1>
    <p class="mt-1 text-sm text-ink-muted">A permanent record of every administrative action.</p>
</div>

<form method="GET" action="{{ route('admin.audit-logs.index') }}" role="search" aria-label="Filter audit logs" data-loading
      class="mt-6 grid gap-4 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <x-admin.select name="action" label="Action">
        <option value="">All actions</option>
        @foreach($actions as $action)
            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.input name="from" type="date" label="From" :value="$filters['from'] ?? ''" />
    <x-admin.input name="to" type="date" label="To" :value="$filters['to'] ?? ''" />
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($auditLogs->isEmpty())
    <x-ui.empty-state class="mt-6" title="No audit entries match these filters." description="Try a different action or date range.">
        @if(array_filter($filters ?? []))
            <x-admin.button variant="secondary" :href="route('admin.audit-logs.index')">Clear filters</x-admin.button>
        @endif
    </x-ui.empty-state>
@else
    <x-ui.table label="Audit logs" class="mt-6">
        <x-slot:head>
            <tr>
                <th scope="col">Time</th><th scope="col">Administrator</th><th scope="col">Action</th><th scope="col">Subject</th><th scope="col">Changes</th><th scope="col">IP address</th>
            </tr>
        </x-slot:head>
        @foreach($auditLogs as $auditLog)
            <tr class="align-top">
                <td data-label="Time" class="whitespace-nowrap">
                    <span>
                        {{ $auditLog->created_at->format('j M Y') }}
                        <span class="block text-xs text-ink-subtle">{{ $auditLog->created_at->format('H:i:s') }}</span>
                    </span>
                </td>
                <td data-label="Administrator" class="font-medium whitespace-nowrap text-ink">{{ $auditLog->user?->name ?? 'System' }}</td>
                <td data-label="Action"><x-admin.badge>{{ $auditLog->action }}</x-admin.badge></td>
                <td data-label="Subject" class="whitespace-nowrap">{{ $auditLog->auditable_type ? class_basename($auditLog->auditable_type).' #'.$auditLog->auditable_id : '—' }}</td>
                <td data-label="Changes">
                    @if($auditLog->old_values || $auditLog->new_values)
                        <details class="group min-w-0 text-left">
                            <summary class="cursor-pointer rounded text-xs font-medium text-primary hover:text-primary-hover">View changes</summary>
                            <div class="mt-2 grid gap-2 sm:min-w-72">
                                @if($auditLog->old_values)
                                    <div><p class="text-[10px] font-semibold tracking-wide text-danger uppercase">Before</p><pre class="mt-1 max-w-[80vw] overflow-x-auto rounded-lg bg-danger-soft p-2 text-xs text-ink sm:max-w-md">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                @endif
                                @if($auditLog->new_values)
                                    <div><p class="text-[10px] font-semibold tracking-wide text-success uppercase">After</p><pre class="mt-1 max-w-[80vw] overflow-x-auto rounded-lg bg-success-soft p-2 text-xs text-ink sm:max-w-md">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                @endif
                            </div>
                        </details>
                    @else
                        <span class="text-ink-subtle">—</span>
                    @endif
                </td>
                <td data-label="IP address" class="font-mono text-xs whitespace-nowrap">{{ $auditLog->ip_address }}</td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($auditLogs->hasPages())
    <div class="mt-4">{{ $auditLogs->links() }}</div>
@endif
@endsection
