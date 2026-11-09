@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $icons = [
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'userPlus' => 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z',
        'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.04A11.96 11.96 0 013.6 6 12 12 0 003 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29z',
        'shieldAlert' => 'M12 9v3.75m0-10.04A11.96 11.96 0 013.6 6 12 12 0 003 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29zm0 13.04h.01v.01H12v-.01z',
        'login' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
        'warning' => 'M12 9v3.75m-9.3 3.38c-.87 1.5.22 3.37 1.95 3.37h14.7c1.73 0 2.82-1.87 1.95-3.37L13.95 3.38c-.87-1.5-3.03-1.5-3.9 0L2.7 16.13zM12 15.75h.01v.01H12v-.01z',
    ];
@endphp

{{-- Header --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-ink-muted">{{ now()->format('l, j F Y') }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $greeting }}, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-ink-muted">Here's what is happening on Cambo Hub today.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @can('create', \App\Models\User::class)
            <x-admin.button :href="route('admin.users.create')">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                Add administrator
            </x-admin.button>
        @endcan
        @can(\App\Enums\PermissionArea::AuditLogs->permission(\App\Enums\PermissionAction::View))
            <x-admin.button variant="secondary" :href="route('admin.audit-logs.index')">View audit logs</x-admin.button>
        @endcan
    </div>
</div>

{{-- Key figures --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 min-[1800px]:grid-cols-6">
    <x-admin.stat label="Registered users" :value="$platformStats['registeredUsers']" :icon="$icons['users']" hint="All accounts on the platform" />
    <x-admin.stat label="New users today" :value="$platformStats['newUsersToday']" :icon="$icons['userPlus']" tone="violet" hint="Registrations since midnight" />
    @if($securityStats)
        <x-admin.stat label="Administrators" :value="$securityStats['administrators']" :icon="$icons['shield']" tone="green"
            :hint="$securityStats['suspendedAdministrators'].' suspended'" :href="route('admin.users.index')" />
        <x-admin.stat label="Admins without 2FA" :value="$securityStats['administratorsWithoutTwoFactor']" :icon="$icons['shieldAlert']"
            :tone="$securityStats['administratorsWithoutTwoFactor'] > 0 ? 'amber' : 'green'" hint="Should be zero in production" />
        <x-admin.stat label="Sign-ins today" :value="$securityStats['signInsToday']" :icon="$icons['login']" hint="Successful sign-ins, all accounts" />
        <x-admin.stat label="Failed sign-ins" :value="$securityStats['failedSignInsLastDay']" :icon="$icons['warning']"
            :tone="$securityStats['failedSignInsLastDay'] > 0 ? 'red' : 'green'"
            :hint="'Last 24 hours · '.$securityStats['suspiciousSignInsLastWeek'].' new-IP sign-ins this week'" />
    @endif
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    {{-- Recent activity --}}
    <section class="overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line xl:col-span-2">
        <header class="flex items-center justify-between gap-4 border-b border-line px-6 py-4">
            <div>
                <h2 class="text-base font-semibold text-ink">Recent admin activity</h2>
                <p class="text-sm text-ink-muted">Latest actions from the audit log.</p>
            </div>
            @if($recentAuditLogs)
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-semibold rounded text-primary hover:text-primary-hover">View all &rarr;</a>
            @endif
        </header>
        @if($recentAuditLogs === null)
            <p class="px-6 py-12 text-center text-sm text-ink-muted">Your role does not include access to the audit log.</p>
        @elseif($recentAuditLogs->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-ink-muted">No activity recorded yet.</p>
        @else
            <ul class="divide-y divide-line">
                @foreach($recentAuditLogs as $auditLog)
                    <li class="flex items-center gap-4 px-6 py-3.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-muted text-xs font-semibold text-ink-muted">
                            {{ mb_strtoupper(mb_substr($auditLog->user?->name ?? 'S', 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-ink-muted">
                                <span class="font-semibold text-ink">{{ $auditLog->user?->name ?? 'System' }}</span>
                                <span class="text-ink-muted">·</span>
                                {{ \Illuminate\Support\Str::of($auditLog->action)->replace(['.', '_'], ' ')->ucfirst() }}
                                @if($auditLog->auditable_type)
                                    <span class="text-ink-muted">on {{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-ink-subtle">{{ $auditLog->ip_address }}</p>
                        </div>
                        <time class="shrink-0 text-xs whitespace-nowrap text-ink-muted" datetime="{{ $auditLog->created_at->toIso8601String() }}">{{ $auditLog->created_at->diffForHumans() }}</time>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div class="space-y-6">
        {{-- Your recent sign-ins --}}
        <section class="overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line">
            <header class="border-b border-line px-6 py-4">
                <h2 class="text-base font-semibold text-ink">Your recent sign-ins</h2>
                <p class="text-sm text-ink-muted">Contact a Super Admin if one wasn't you.</p>
            </header>
            <ul class="divide-y divide-line">
                @forelse($recentSignIns as $signIn)
                    <li class="flex items-center justify-between gap-3 px-6 py-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm text-ink-muted">{{ $signIn->ip_address }}</p>
                            <p class="text-xs text-ink-subtle">{{ $signIn->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex shrink-0 gap-1.5">
                            @if($signIn->is_suspicious)<x-admin.badge color="amber">New IP</x-admin.badge>@endif
                            @if($signIn->successful)
                                <x-admin.badge color="green">Success</x-admin.badge>
                            @else
                                <x-admin.badge color="red">Failed</x-admin.badge>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-ink-muted">No sign-ins recorded yet.</li>
                @endforelse
            </ul>
        </section>

        {{-- Access --}}
        <section class="rounded-2xl bg-surface p-6 shadow-card ring-1 ring-line">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">Your access</h2>
                <x-admin.badge>{{ auth()->user()->roleLabel() }}</x-admin.badge>
            </div>
            <ul class="mt-4 divide-y divide-line">
                @forelse($areaAccess as $areaLabel => $level)
                    <li class="flex items-center justify-between py-2 text-sm text-ink-muted">
                        {{ $areaLabel }}
                        <x-admin.access-level :level="$level" />
                    </li>
                @empty
                    <li class="py-2 text-sm text-ink-muted">Your role has no permissions yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
