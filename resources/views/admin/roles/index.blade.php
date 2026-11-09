@extends('layouts.admin')
@section('title', 'Roles & permissions')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Roles</h1>
        <p class="mt-1 text-sm text-slate-500">Reusable permission templates. Define a role once, then assign it to administrators.</p>
    </div>
    @can('create', \App\Models\Role::class)
        <x-admin.button :href="route('admin.roles.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Create role
        </x-admin.button>
    @endcan
</div>

{{-- Legend --}}
<div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
    <span class="flex items-center gap-1.5"><x-admin.access-level level="full" /> all actions</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="manage" /> some write access</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="view" /> read-only</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="none" /> no access</span>
    <span class="text-slate-400">· hover a cell for the exact permissions</span>
</div>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="sticky left-0 z-10 min-w-56 bg-slate-50 px-5 py-3 text-left">Role</th>
                    @foreach($areas as $area)
                        <th class="px-3 py-3 text-center whitespace-nowrap">{{ $area->label() }}</th>
                    @endforeach
                    <th class="sticky right-0 z-10 bg-slate-50 px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($roles as $role)
                    <tr class="group hover:bg-slate-50/70">
                        <td class="sticky left-0 z-10 bg-white px-5 py-3.5 group-hover:bg-slate-50">
                            @can('update', $role)
                                <a href="{{ route('admin.roles.edit', $role) }}" class="font-semibold whitespace-nowrap text-slate-900 hover:text-brand-700">{{ $role->label() }}</a>
                            @else
                                <span class="font-semibold whitespace-nowrap text-slate-900">{{ $role->label() }}</span>
                            @endcan
                            <div class="mt-1 flex items-center gap-2 text-xs whitespace-nowrap text-slate-500">
                                @if($role->isLocked())
                                    <x-admin.badge color="violet">Locked</x-admin.badge>
                                @elseif($role->isSystem())
                                    <x-admin.badge color="gray">Built-in</x-admin.badge>
                                @else
                                    <x-admin.badge>Custom</x-admin.badge>
                                @endif
                                {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('administrator', $role->users_count) }}
                            </div>
                        </td>
                        @foreach($areas as $area)
                            @php($granted = array_values(array_intersect($area->permissions(), $role->permissionNames())))
                            <td class="px-3 py-3.5 text-center" title="{{ $granted === [] ? 'No access' : $area->label().': '.collect($granted)->map(fn ($name) => \Illuminate\Support\Str::after($name, '.'))->implode(', ') }}">
                                <x-admin.access-level :level="$role->accessLevel($area)" />
                            </td>
                        @endforeach
                        <td class="sticky right-0 z-10 bg-white px-5 py-3.5 text-right whitespace-nowrap group-hover:bg-slate-50">
                            @can('update', $role)
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($areas) + 2 }}" class="px-6 py-12 text-center text-slate-500">No roles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="mt-4 text-xs text-slate-500">
    The Super Admin role is locked and always has every permission. Administrators can't edit their own role.
    @cannot(\App\Enums\PermissionArea::Roles->permission(\App\Enums\PermissionAction::Edit))
        <span class="font-medium text-amber-700">You can view roles but not change them.</span>
    @endcannot
</p>
@endsection
