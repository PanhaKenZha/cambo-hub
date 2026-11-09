@extends('layouts.admin')
@section('title', $admin->name)
@section('content')
<a href="{{ route('admin.users.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Administrators</a>

<div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 items-center gap-4">
        <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-soft text-lg font-semibold text-primary">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight break-words text-ink">{{ $admin->name }}</h1>
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
            <div><dt class="text-ink-muted">Email</dt><dd class="mt-1 font-medium break-all text-ink">{{ $admin->email }}</dd></div>
            <div>
                <dt class="text-ink-muted">Two-factor authentication</dt>
                <dd class="mt-1">@if($admin->hasTwoFactorEnabled())<x-admin.badge color="green">Enabled</x-admin.badge>@else<x-admin.badge color="amber">Not set up</x-admin.badge>@endif</dd>
            </div>
            <div>
                <dt class="text-ink-muted">Last sign-in</dt>
                <dd class="mt-1 font-medium text-ink">{{ $admin->last_login_at?->toDayDateTimeString() ?? 'Never' }}</dd>
                @if($admin->last_login_ip)<dd class="text-ink-muted">from {{ $admin->last_login_ip }}</dd>@endif
            </div>
            <div>
                <dt class="text-ink-muted">Status</dt>
                @if($admin->isSuspended())
                    <dd class="mt-1 font-medium text-danger">Suspended {{ $admin->suspended_at->diffForHumans() }}</dd>
                    <dd class="text-ink-muted">Reason: {{ $admin->suspension_reason }}</dd>
                @else
                    <dd class="mt-1 font-medium text-ink">Active</dd>
                @endif
            </div>
        </dl>
    </x-admin.card>

    @can('suspend', $admin)
        <x-admin.card title="Account actions" class="ring-danger/30!">
            <div class="space-y-6">
                @if($admin->isSuspended())
                    <form method="POST" action="{{ route('admin.users.unsuspend', $admin) }}" data-loading>
                        @csrf
                        <p class="mb-3 text-sm text-ink-muted">Restore this administrator's access.</p>
                        <x-admin.button class="w-full">Reactivate account</x-admin.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $admin) }}" class="space-y-3" data-loading>
                        @csrf
                        <x-ui.error-summary />
                        <x-admin.input name="reason" label="Suspension reason" maxlength="255" placeholder="Why is this account being suspended?" required />
                        <x-admin.button variant="danger" class="w-full">Suspend and sign out</x-admin.button>
                    </form>
                @endif
                @if($admin->hasTwoFactorEnabled())
                    <form method="POST" action="{{ route('admin.users.reset-two-factor', $admin) }}" class="border-t border-line pt-6" data-loading>
                        @csrf
                        <p class="mb-3 text-sm text-ink-muted">Use if they lost their authenticator device.</p>
                        <x-admin.button variant="secondary" class="w-full">Reset two-factor</x-admin.button>
                    </form>
                @endif
            </div>
        </x-admin.card>
    @endcan
</div>

<section class="mt-8" aria-labelledby="sign-in-activity-heading">
    <h2 id="sign-in-activity-heading" class="text-base font-semibold text-ink">Recent sign-in activity</h2>
    @if($loginHistories->isEmpty())
        <x-ui.empty-state class="mt-3" title="No sign-in activity yet." />
    @else
        <x-ui.table label="Recent sign-in activity" class="mt-3">
            <x-slot:head>
                <tr>
                    <th scope="col">Time</th><th scope="col">Result</th><th scope="col">IP address</th><th scope="col">Device</th>
                </tr>
            </x-slot:head>
            @foreach($loginHistories as $loginHistory)
                <tr>
                    <td data-label="Time" class="whitespace-nowrap">{{ $loginHistory->created_at->toDayDateTimeString() }}</td>
                    <td data-label="Result">
                        <div class="flex flex-wrap justify-end gap-1.5 sm:justify-start">
                            @if($loginHistory->successful)
                                <x-admin.badge color="green">Success</x-admin.badge>
                            @else
                                <x-admin.badge color="red">Failed: {{ str_replace('_', ' ', $loginHistory->failure_reason) }}</x-admin.badge>
                            @endif
                            @if($loginHistory->is_suspicious)<x-admin.badge color="amber">New IP</x-admin.badge>@endif
                        </div>
                    </td>
                    <td data-label="IP address" class="font-mono text-xs">{{ $loginHistory->ip_address }}</td>
                    <td data-label="Device" class="sm:max-w-xs sm:truncate" title="{{ $loginHistory->user_agent }}"><span class="min-w-0 break-all sm:break-normal">{{ $loginHistory->user_agent }}</span></td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</section>
@endsection
