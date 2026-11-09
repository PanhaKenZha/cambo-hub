@extends('layouts.admin')
@section('title', 'Audit logs')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit logs</h1>
    <p class="mt-1 text-sm text-slate-500">A permanent record of every administrative action.</p>
</div>

<form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mt-6 grid gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
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

<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Time</th><th class="px-6 py-3">Administrator</th><th class="px-6 py-3">Action</th><th class="px-6 py-3">Subject</th><th class="px-6 py-3">Changes</th><th class="px-6 py-3">IP address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($auditLogs as $auditLog)
                    <tr class="align-top">
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            {{ $auditLog->created_at->format('j M Y') }}
                            <span class="block text-xs text-slate-400">{{ $auditLog->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-6 py-4 font-medium whitespace-nowrap text-slate-900">{{ $auditLog->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-4"><x-admin.badge>{{ $auditLog->action }}</x-admin.badge></td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $auditLog->auditable_type ? class_basename($auditLog->auditable_type).' #'.$auditLog->auditable_id : '—' }}</td>
                        <td class="px-6 py-4">
                            @if($auditLog->old_values || $auditLog->new_values)
                                <details class="group">
                                    <summary class="cursor-pointer text-xs font-medium text-brand-600 hover:text-brand-800">View changes</summary>
                                    <div class="mt-2 grid min-w-72 gap-2">
                                        @if($auditLog->old_values)
                                            <div><p class="text-[10px] font-semibold tracking-wide text-red-600 uppercase">Before</p><pre class="mt-1 overflow-x-auto rounded-lg bg-red-50/60 p-2 text-xs text-slate-700">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                        @endif
                                        @if($auditLog->new_values)
                                            <div><p class="text-[10px] font-semibold tracking-wide text-emerald-600 uppercase">After</p><pre class="mt-1 overflow-x-auto rounded-lg bg-emerald-50/60 p-2 text-xs text-slate-700">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                        @endif
                                    </div>
                                </details>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-xs whitespace-nowrap text-slate-600">{{ $auditLog->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No audit entries match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $auditLogs->links() }}</div>
    @endif
</div>
@endsection
