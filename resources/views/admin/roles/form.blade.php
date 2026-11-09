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
        <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? 'Edit role' : 'Create role' }}</h1>
        <p class="mt-1 text-sm text-ink-muted">{{ $isEditing ? 'Update the role details and permissions.' : 'Define a reusable set of permissions, then assign it to administrators.' }}</p>
    </div>
    <x-admin.button variant="secondary" :href="route('admin.roles.index')">&larr; Back to list</x-admin.button>
</div>

<form method="POST" action="{{ $isEditing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" id="role-form" class="mt-6 space-y-6" data-loading>
    <x-ui.error-summary :ids="['permissions' => 'permissions-heading']" />
    @csrf
    @if($isEditing) @method('PUT') @endif

    {{-- Role information --}}
    <x-admin.card title="Role information">
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                @if($isEditing && $role->isSystem())
                    <label for="name" class="block text-sm font-medium text-ink">Role name</label>
                    <input id="name" value="{{ $role->label() }}" disabled aria-describedby="name-hint" class="form-control mt-1.5 rounded-lg bg-surface-muted text-ink-muted">
                    <p id="name-hint" class="mt-1.5 text-xs text-ink-muted">Built-in roles cannot be renamed.</p>
                @else
                    <x-admin.input name="name" label="Role name" :value="$role?->name" maxlength="50" placeholder="e.g. Content Editor" required />
                @endif
            </div>
            <x-admin.input name="description" label="Description" :value="$role?->description ?? ($role?->summary())" maxlength="255" placeholder="What is this role for?" />
        </div>
        @if($isEditing)
            <p class="mt-4 text-xs text-ink-muted">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('administrator', $role->users_count) }} currently {{ $role->users_count === 1 ? 'has' : 'have' }} this role. Changes apply to them immediately after saving.</p>
        @endif
    </x-admin.card>

    {{-- Permissions --}}
    <section class="overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line" data-permission-table>
        <header class="flex flex-col gap-3 border-b border-line px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="permissions-heading" tabindex="-1" class="text-base font-semibold text-ink focus:outline-none">Permissions</h2>
                <p class="mt-0.5 text-sm text-ink-muted">Tick what this role may do in each area. Changes apply as soon as you save.</p>
            </div>
            @if($isEditing && $role->builtInRole())
                <button type="submit" form="reset-role-form" class="cursor-pointer rounded-lg px-3 py-1.5 text-sm font-semibold text-danger ring-1 ring-danger/30 hover:bg-danger-soft"
                        onclick="return confirm('Reset {{ $role->label() }} to its default permissions?')">Reset to default</button>
            @endif
        </header>

        <div class="flex flex-wrap items-center gap-2 px-6 pt-4">
            <button type="button" data-select="all" class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold text-ink ring-1 ring-line-strong hover:bg-surface-muted">Select all</button>
            <button type="button" data-select="none" class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold text-ink ring-1 ring-line-strong hover:bg-surface-muted">Clear all</button>
            @if($hasRestrictedPermissions)
                <span class="text-xs text-warning">Greyed-out permissions are ones you don't have yourself, so you can't change them.</span>
            @endif
            @error('permissions')<span class="text-xs font-medium text-danger" role="alert">{{ $message }}</span>@enderror
            @error('permissions.*')<span class="text-xs font-medium text-danger" role="alert">{{ $message }}</span>@enderror
        </div>

        <div class="mt-4 overflow-x-auto" role="region" aria-labelledby="permissions-heading" tabindex="0">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-y border-line bg-surface-muted text-xs font-semibold tracking-wide text-ink-muted uppercase">
                        <th scope="col" class="px-6 py-3 text-left">Area</th>
                        @foreach(PermissionAction::cases() as $action)
                            <th scope="col" class="px-4 py-3 text-center" title="{{ $action->description() }}">{{ $action->label() }}</th>
                        @endforeach
                        <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Toggle row</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areaGroups as $groupName => $areas)
                        <tr class="bg-surface-muted/60">
                            <td colspan="{{ count(PermissionAction::cases()) + 2 }}" class="px-6 py-2 text-[11px] font-semibold tracking-[0.12em] text-primary uppercase">{{ $groupName }}</td>
                        </tr>
                        @foreach($areas as $area)
                            <tr class="border-t border-line hover:bg-surface-muted/60" data-permission-row>
                                <td class="px-6 py-3 font-medium whitespace-nowrap text-ink">{{ $area->label() }}</td>
                                @foreach(PermissionAction::cases() as $action)
                                    <td class="px-4 py-3 text-center">
                                        @if($area->supports($action))
                                            @php($permissionName = $area->permission($action))
                                            @php($canChange = in_array($permissionName, $grantablePermissions, true))
                                            <input type="checkbox" name="permissions[]" value="{{ $permissionName }}"
                                                   aria-label="{{ $area->label() }}: {{ $action->label() }}"
                                                   @checked(in_array($permissionName, $selectedPermissions, true))
                                                   @disabled(! $canChange)
                                                   class="size-4.5 cursor-pointer rounded border-line-strong accent-brand-600 dark:accent-brand-300 disabled:cursor-not-allowed disabled:opacity-40">
                                        @else
                                            <span class="text-ink-subtle/60" aria-hidden="true">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 text-right">
                                    <button type="button" data-toggle-row class="cursor-pointer text-xs font-semibold rounded text-primary hover:text-primary-hover">all</button>
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

<div class="sticky bottom-0 mt-6 -mx-4 flex flex-col-reverse gap-3 border-t border-line bg-surface/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:-mx-8 lg:px-8">
    <div>
        @if($isEditing)
            @can('delete', $role)
                <button type="submit" form="delete-role-form" onclick="return confirm('Delete the role {{ $role->name }}? This cannot be undone.')"
                        class="cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete role</button>
            @elseif(! $role->isSystem())
                <span class="text-xs text-ink-muted">Remove this role from all administrators before deleting it.</span>
            @endcan
        @endif
    </div>
    <div class="flex flex-wrap gap-3">
        <x-admin.button variant="secondary" :href="route('admin.roles.index')">Cancel</x-admin.button>
        <x-admin.button form="role-form" loading-text="Saving…">{{ $isEditing ? 'Save changes' : 'Create role' }}</x-admin.button>
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
