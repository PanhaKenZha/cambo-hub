@extends('layouts.admin')
@section('title', 'Administrators')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Administrators</h1>
        <p class="mt-1 text-sm text-slate-500">Manage who can access the admin panel and what they can do.</p>
    </div>
    @can('create', \App\Models\User::class)
        <x-admin.button :href="route('admin.users.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Add administrator
        </x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="mt-6 grid gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
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

<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Administrator</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">2FA</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Last sign-in</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($admins as $admin)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.show', $admin) }}" class="group flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                                <span>
                                    <span class="block font-medium text-slate-900 group-hover:text-brand-700">{{ $admin->name }}</span>
                                    <span class="block text-slate-500">{{ $admin->email }}</span>
                                </span>
                            </a>
                        </td>
                        <td class="px-6 py-4"><x-admin.badge>{{ $admin->roleLabel() }}</x-admin.badge></td>
                        <td class="px-6 py-4">
                            @if($admin->hasTwoFactorEnabled())<x-admin.badge color="green">On</x-admin.badge>@else<x-admin.badge color="amber">Not set up</x-admin.badge>@endif
                        </td>
                        <td class="px-6 py-4">
                            @if($admin->isSuspended())<x-admin.badge color="red">Suspended</x-admin.badge>@else<x-admin.badge color="green">Active</x-admin.badge>@endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No administrators match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $admins->links() }}</div>
    @endif
</div>
@endsection
