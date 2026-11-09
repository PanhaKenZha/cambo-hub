@extends('layouts.admin')
@section('title', 'Roles & permissions')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Roles</h1>
        <p class="mt-1 text-sm text-ink-muted">Reusable permission templates. Define a role once, then assign it to administrators.</p>
    </div>
    @can('create', \App\Models\Role::class)
        <x-admin.button :href="route('admin.roles.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            Create role
        </x-admin.button>
    @endcan
</div>

{{-- Legend --}}
<div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-ink-muted">
    <span class="flex items-center gap-1.5"><x-admin.access-level level="full" /> all actions</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="manage" /> some write access</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="view" /> read-only</span>
    <span class="flex items-center gap-1.5"><x-admin.access-level level="none" /> no access</span>
    <span class="text-ink-subtle">· hover a cell for the exact permissions</span>
</div>

@if($roles->isEmpty())
    <x-ui.empty-state class="mt-4" title="No roles yet." description="Create a role to group permissions you can assign to administrators.">
        @can('create', \App\Models\Role::class)
            <x-admin.button :href="route('admin.roles.create')">Create role</x-admin.button>
        @endcan
    </x-ui.empty-state>
@else
    <x-ui.table label="Roles and their access to each area" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col" class="left-0 z-10 min-w-56 bg-surface-muted sm:sticky">Role</th>
                @foreach($areas as $area)
                    <th scope="col" class="text-center whitespace-nowrap">{{ $area->label() }}</th>
                @endforeach
                <th scope="col" class="right-0 z-10 bg-surface-muted text-right sm:sticky"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($roles as $role)
            <tr class="group hover:bg-surface-muted">
                <td data-label="Role" class="left-0 z-10 bg-surface group-hover:bg-surface-muted sm:sticky">
                    <span class="block">
                        @can('update', $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="rounded font-semibold whitespace-nowrap text-ink hover:text-primary">{{ $role->label() }}</a>
                        @else
                            <span class="font-semibold whitespace-nowrap text-ink">{{ $role->label() }}</span>
                        @endcan
                        <span class="mt-1 flex items-center gap-2 text-xs whitespace-nowrap text-ink-muted">
                            @if($role->isLocked())
                                <x-admin.badge color="violet">Locked</x-admin.badge>
                            @elseif($role->isSystem())
                                <x-admin.badge color="gray">Built-in</x-admin.badge>
                            @else
                                <x-admin.badge>Custom</x-admin.badge>
                            @endif
                            {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('administrator', $role->users_count) }}
                        </span>
                    </span>
                </td>
                @foreach($areas as $area)
                    @php($granted = array_values(array_intersect($area->permissions(), $role->permissionNames())))
                    <td data-label="{{ $area->label() }}" class="sm:text-center" title="{{ $granted === [] ? 'No access' : $area->label().': '.collect($granted)->map(fn ($name) => \Illuminate\Support\Str::after($name, '.'))->implode(', ') }}">
                        <x-admin.access-level :level="$role->accessLevel($area)" />
                    </td>
                @endforeach
                <td data-label="Actions" class="right-0 z-10 bg-surface text-right whitespace-nowrap group-hover:bg-surface-muted sm:sticky">
                    @can('update', $role)
                        <a href="{{ route('admin.roles.edit', $role) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit<span class="sr-only"> {{ $role->label() }}</span></a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

<p class="mt-4 text-xs text-ink-muted">
    The Super Admin role is locked and always has every permission. Administrators can't edit their own role.
    @cannot(\App\Enums\PermissionArea::Roles->permission(\App\Enums\PermissionAction::Edit))
        <span class="font-medium text-warning">You can view roles but not change them.</span>
    @endcannot
</p>
@endsection
