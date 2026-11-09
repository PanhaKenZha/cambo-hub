@extends('layouts.admin')
@section('title', $admin->name)
@section('content')
<a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Administrators</a>

<div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-4">
        <span class="flex size-14 items-center justify-center rounded-full bg-brand-100 text-lg font-semibold text-brand-700">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $admin->name }}</h1>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <x-admin.badge>{{ $admin->roleLabel() }}</x-admin.badge>
                @if($admin->isSuspended())<x-admin.badge color="red">Suspended</x-admin.badge>@else<x-admin.badge color="green">Active</x-admin.badge>@endif
            </div>
        </div>
    </div>
    @can('update', $admin)
        <x-admin.button variant="secondary" :href="route('admin.users.edit', $admin)">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m5.43 13.83-.79 3.15a.5.5 0 0 0 .61.61l3.15-.79a2 2 0 0 0 .93-.53l6.95-6.95a2 2 0 0 0 0-2.83l-.94-.94a2 2 0 0 0-2.83 0L5.96 12.9a2 2 0 0 0-.53.93Z"/></svg>
            Edit
        </x-admin.button>
    @endcan
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    <x-admin.card title="Account details" class="lg:col-span-2">
        <dl class="grid gap-x-6 gap-y-5 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Email</dt><dd class="mt-1 font-medium break-all text-slate-900">{{ $admin->email }}</dd></div>
            <div>
                <dt class="text-slate-500">Two-factor authentication</dt>
                <dd class="mt-1">@if($admin->hasTwoFactorEnabled())<x-admin.badge color="green">Enabled</x-admin.badge>@else<x-admin.badge color="amber">Not set up</x-admin.badge>@endif</dd>
            </div>
            <div>
                <dt class="text-slate-500">Last sign-in</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $admin->last_login_at?->toDayDateTimeString() ?? 'Never' }}</dd>
                @if($admin->last_login_ip)<dd class="text-slate-500">from {{ $admin->last_login_ip }}</dd>@endif
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                @if($admin->isSuspended())
                    <dd class="mt-1 font-medium text-red-700">Suspended {{ $admin->suspended_at->diffForHumans() }}</dd>
                    <dd class="text-slate-600">Reason: {{ $admin->suspension_reason }}</dd>
                @else
                    <dd class="mt-1 font-medium text-slate-900">Active</dd>
                @endif
            </div>
        </dl>
    </x-admin.card>

    @can('suspend', $admin)
        <x-admin.card title="Account actions" class="ring-red-200!">
            <div class="space-y-6">
                @if($admin->isSuspended())
                    <form method="POST" action="{{ route('admin.users.unsuspend', $admin) }}">
                        @csrf
                        <p class="mb-3 text-sm text-slate-500">Restore this administrator's access.</p>
                        <x-admin.button class="w-full">Reactivate account</x-admin.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $admin) }}" class="space-y-3">
                        @csrf
                        <x-admin.input name="reason" label="Suspension reason" maxlength="255" placeholder="Why is this account being suspended?" required />
                        <x-admin.button variant="danger" class="w-full">Suspend and sign out</x-admin.button>
                    </form>
                @endif
                @if($admin->hasTwoFactorEnabled())
                    <form method="POST" action="{{ route('admin.users.reset-two-factor', $admin) }}" class="border-t border-slate-100 pt-6">
                        @csrf
                        <p class="mb-3 text-sm text-slate-500">Use if they lost their authenticator device.</p>
                        <x-admin.button variant="secondary" class="w-full">Reset two-factor</x-admin.button>
                    </form>
                @endif
            </div>
        </x-admin.card>
    @endcan
</div>

<div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <header class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-900">Recent sign-in activity</h2>
    </header>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Time</th><th class="px-6 py-3">Result</th><th class="px-6 py-3">IP address</th><th class="px-6 py-3">Device</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($loginHistories as $loginHistory)
                    <tr>
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $loginHistory->created_at->toDayDateTimeString() }}</td>
                        <td class="px-6 py-3.5">
                            <div class="flex flex-wrap gap-1.5">
                                @if($loginHistory->successful)
                                    <x-admin.badge color="green">Success</x-admin.badge>
                                @else
                                    <x-admin.badge color="red">Failed: {{ str_replace('_', ' ', $loginHistory->failure_reason) }}</x-admin.badge>
                                @endif
                                @if($loginHistory->is_suspicious)<x-admin.badge color="amber">New IP</x-admin.badge>@endif
                            </div>
                        </td>
                        <td class="px-6 py-3.5 font-mono text-xs text-slate-600">{{ $loginHistory->ip_address }}</td>
                        <td class="max-w-xs truncate px-6 py-3.5 text-slate-500" title="{{ $loginHistory->user_agent }}">{{ $loginHistory->user_agent }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">No sign-in activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
