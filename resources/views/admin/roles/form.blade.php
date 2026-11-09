@extends('layouts.admin')
@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $isEditing = $role !== null;
    $selectedPermissions = old('permissions', $grantedPermissions);
    $hasRestrictedPermissions = count($grantablePermissions) < count(PermissionArea::allPermissions());
    $areaGroups = collect(PermissionArea::cases())->groupBy(fn (PermissionArea $area) => $area->group());
@endphp
@section('title', $isEditing ? 'Edit '.$role->label() : 'Create role')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $isEditing ? 'Edit role' : 'Create role' }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $isEditing ? 'Update the role details and permissions.' : 'Define a reusable set of permissions, then assign it to administrators.' }}</p>
    </div>
    <x-admin.button variant="secondary" :href="route('admin.roles.index')">&larr; Back to list</x-admin.button>
</div>

<form method="POST" action="{{ $isEditing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" id="role-form" class="mt-6 space-y-6">
    @csrf
    @if($isEditing) @method('PUT') @endif

    {{-- Role information --}}
    <x-admin.card title="Role information">
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                @if($isEditing && $role->isSystem())
                    <label for="name" class="block text-sm font-medium text-slate-700">Role name</label>
                    <input id="name" value="{{ $role->label() }}" disabled class="mt-1.5 block w-full cursor-not-allowed rounded-lg border-0 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-500 ring-1 ring-slate-200 ring-inset">
                    <p class="mt-1.5 text-xs text-slate-500">Built-in roles cannot be renamed.</p>
                @else
                    <x-admin.input name="name" label="Role name" :value="$role?->name" maxlength="50" placeholder="e.g. Content Editor" required />
                @endif
            </div>
            <x-admin.input name="description" label="Description" :value="$role?->description ?? ($role?->summary())" maxlength="255" placeholder="What is this role for?" />
        </div>
        @if($isEditing)
            <p class="mt-4 text-xs text-slate-500">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('administrator', $role->users_count) }} currently {{ $role->users_count === 1 ? 'has' : 'have' }} this role. Changes apply to them immediately after saving.</p>
        @endif
    </x-admin.card>

    {{-- Permissions --}}
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200" data-permission-table>
        <header class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Permissions</h2>
                <p class="mt-0.5 text-sm text-slate-500">Tick what this role may do in each area. Changes apply as soon as you save.</p>
            </div>
            @if($isEditing && $role->builtInRole())
                <button type="submit" form="reset-role-form" class="cursor-pointer rounded-lg px-3 py-1.5 text-sm font-semibold text-red-600 ring-1 ring-red-200 hover:bg-red-50"
                        onclick="return confirm('Reset {{ $role->label() }} to its default permissions?')">Reset to default</button>
            @endif
        </header>

        <div class="flex flex-wrap items-center gap-2 px-6 pt-4">
            <button type="button" data-select="all" class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">Select all</button>
            <button type="button" data-select="none" class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">Clear all</button>
            @if($hasRestrictedPermissions)
                <span class="text-xs text-amber-700">Greyed-out permissions are ones you don't have yourself, so you can't change them.</span>
            @endif
            @error('permissions')<span class="text-xs text-red-600" role="alert">{{ $message }}</span>@enderror
            @error('permissions.*')<span class="text-xs text-red-600" role="alert">{{ $message }}</span>@enderror
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-y border-slate-200 bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <th class="px-6 py-3 text-left">Area</th>
                        @foreach(PermissionAction::cases() as $action)
                            <th class="px-4 py-3 text-center" title="{{ $action->description() }}">{{ $action->label() }}</th>
                        @endforeach
                        <th class="px-6 py-3 text-right"><span class="sr-only">Toggle row</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areaGroups as $groupName => $areas)
                        <tr class="bg-slate-50/60">
                            <td colspan="{{ count(PermissionAction::cases()) + 2 }}" class="px-6 py-2 text-[11px] font-semibold tracking-[0.12em] text-brand-600 uppercase">{{ $groupName }}</td>
                        </tr>
                        @foreach($areas as $area)
                            <tr class="border-t border-slate-100 hover:bg-slate-50/70" data-permission-row>
                                <td class="px-6 py-3 font-medium whitespace-nowrap text-slate-800">{{ $area->label() }}</td>
                                @foreach(PermissionAction::cases() as $action)
                                    <td class="px-4 py-3 text-center">
                                        @if($area->supports($action))
                                            @php($permissionName = $area->permission($action))
                                            @php($canChange = in_array($permissionName, $grantablePermissions, true))
                                            <input type="checkbox" name="permissions[]" value="{{ $permissionName }}"
                                                   aria-label="{{ $area->label() }}: {{ $action->label() }}"
                                                   @checked(in_array($permissionName, $selectedPermissions, true))
                                                   @disabled(! $canChange)
                                                   class="size-4.5 cursor-pointer rounded border-slate-300 accent-brand-600 disabled:cursor-not-allowed disabled:opacity-40">
                                        @else
                                            <span class="text-slate-300" aria-hidden="true">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 text-right">
                                    <button type="button" data-toggle-row class="cursor-pointer text-xs font-semibold text-brand-600 hover:text-brand-800">all</button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</form>

@if($isEditing && $role->builtInRole())
    <form method="POST" action="{{ route('admin.roles.reset', $role) }}" id="reset-role-form" class="hidden">@csrf</form>
@endif
@if($isEditing)
    @can('delete', $role)
        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" id="delete-role-form" class="hidden">@csrf @method('DELETE')</form>
    @endcan
@endif

<div class="sticky bottom-0 mt-6 -mx-4 flex flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:-mx-8 lg:px-8">
    <div>
        @if($isEditing)
            @can('delete', $role)
                <button type="submit" form="delete-role-form" onclick="return confirm('Delete the role {{ $role->name }}? This cannot be undone.')"
                        class="cursor-pointer text-sm font-semibold text-red-600 hover:text-red-800">Delete role</button>
            @elseif(! $role->isSystem())
                <span class="text-xs text-slate-500">Remove this role from all administrators before deleting it.</span>
            @endcan
        @endif
    </div>
    <div class="flex gap-3">
        <x-admin.button variant="secondary" :href="route('admin.roles.index')">Cancel</x-admin.button>
        <x-admin.button form="role-form">{{ $isEditing ? 'Save changes' : 'Create role' }}</x-admin.button>
    </div>
</div>

<script>
    (() => {
        const table = document.querySelector('[data-permission-table]');
        const editable = (scope) => [...scope.querySelectorAll('input[type=checkbox]:not(:disabled)')];

        table.querySelectorAll('[data-select]').forEach((button) => {
            button.addEventListener('click', () => {
                editable(table).forEach((checkbox) => { checkbox.checked = button.dataset.select === 'all'; });
            });
        });

        table.querySelectorAll('[data-permission-row]').forEach((row) => {
            row.querySelector('[data-toggle-row]').addEventListener('click', () => {
                const checkboxes = editable(row);
                const selectAll = ! checkboxes.every((checkbox) => checkbox.checked);
                checkboxes.forEach((checkbox) => { checkbox.checked = selectAll; });
            });
        });
    })();
</script>
@endsection
