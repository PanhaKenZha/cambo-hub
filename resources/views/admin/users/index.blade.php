@extends('layouts.admin')
@section('title', 'Administrators')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Administrators</h1>
        <p class="mt-1 text-sm text-ink-muted">Manage who can access the admin panel and what they can do.</p>
    </div>
    @can('create', \App\Models\User::class)
        <x-admin.button :href="route('admin.users.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Add administrator
        </x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.users.index') }}" role="search" aria-label="Filter administrators" data-loading
      class="mt-6 grid gap-4 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Name or email" />
    <x-admin.select name="role" label="Role">
        <option value="">All roles</option>
        @foreach($roles as $role)
            <option value="{{ $role->name }}" @selected(($filters['role'] ?? '') === $role->name)>{{ $role->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="status" label="Status">
        <option value="">Any status</option>
        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
        <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspended</option>
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($admins->isEmpty())
    <x-ui.empty-state class="mt-6" title="No administrators match these filters." description="Try a different search, role or status.">
        @if(array_filter($filters ?? []))
            <x-admin.button variant="secondary" :href="route('admin.users.index')">Clear filters</x-admin.button>
        @endif
    </x-ui.empty-state>
@else
    <x-ui.table label="Administrators" class="mt-6">
        <x-slot:head>
            <tr>
                <th scope="col">Administrator</th>
                <th scope="col">Role</th>
                <th scope="col">2FA</th>
                <th scope="col">Status</th>
                <th scope="col">Last sign-in</th>
            </tr>
        </x-slot:head>
        @foreach($admins as $admin)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Administrator">
                    <a href="{{ route('admin.users.show', $admin) }}" class="group flex min-w-0 items-center gap-3 rounded-lg text-left">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-primary">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                        <span class="min-w-0">
                            <span class="block font-medium text-ink group-hover:text-primary">{{ $admin->name }}</span>
                            <span class="block break-all text-ink-muted">{{ $admin->email }}</span>
                        </span>
                    </a>
                </td>
                <td data-label="Role"><x-admin.badge>{{ $admin->roleLabel() }}</x-admin.badge></td>
                <td data-label="2FA">
                    @if($admin->hasTwoFactorEnabled())<x-admin.badge color="green">On</x-admin.badge>@else<x-admin.badge color="amber">Not set up</x-admin.badge>@endif
                </td>
                <td data-label="Status">
                    @if($admin->isSuspended())<x-admin.badge color="red">Suspended</x-admin.badge>@else<x-admin.badge color="green">Active</x-admin.badge>@endif
                </td>
                <td data-label="Last sign-in" class="whitespace-nowrap">{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($admins->hasPages())
    <div class="mt-4">{{ $admins->links() }}</div>
@endif
@endsection
