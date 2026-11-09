<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Role::class);

        return view('admin.roles.index', [
            'roles' => Role::sortForDisplay(Role::with('permissions')->withCount('users')->get()),
            'areas' => PermissionArea::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Role::class);

        return view('admin.roles.form', [
            'role' => null,
            'grantedPermissions' => [],
            'grantablePermissions' => $this->grantablePermissions($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'web'), Rule::notIn(AdminRole::values())],
            'description' => ['nullable', 'string', 'max:255'],
            ...$this->permissionRules(),
        ]);

        $role = DB::transaction(function () use ($request, $data): Role {
            $role = Role::create(['name' => trim($data['name']), 'guard_name' => 'web', 'description' => $data['description'] ?? null]);
            $permissions = $this->permissionsAfterChange($request->user(), [], $data['permissions'] ?? []);
            $role->syncPermissions($permissions);

            $this->auditLogger->log('role.created', $role, newValues: [
                'name' => $role->name, 'description' => $role->description, 'permissions' => $permissions,
            ]);

            return $role;
        });

        return redirect()->route('admin.roles.index')->with('status', "Role \"{$role->label()}\" created.");
    }

    public function edit(Request $request, Role $role): View
    {
        Gate::authorize('update', $role);

        return view('admin.roles.form', [
            'role' => $role->loadCount('users'),
            'grantedPermissions' => $role->permissionNames(),
            'grantablePermissions' => $this->grantablePermissions($request->user()),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);
        $data = $request->validate([
            'name' => $role->isSystem()
                ? ['prohibited']
                : ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id), Rule::notIn(AdminRole::values())],
            'description' => ['nullable', 'string', 'max:255'],
            ...$this->permissionRules(),
        ]);

        DB::transaction(function () use ($request, $role, $data): void {
            $oldValues = ['name' => $role->name, 'description' => $role->description, 'permissions' => $role->permissionNames()];
            $permissions = $this->permissionsAfterChange($request->user(), $role->permissionNames(), $data['permissions'] ?? []);

            $role->fill(['description' => $data['description'] ?? null]);
            if (! $role->isSystem()) {
                $role->name = trim($data['name']);
            }
            $role->save();
            $role->syncPermissions($permissions);

            $this->auditLogger->log('role.updated', $role, $oldValues, [
                'name' => $role->name, 'description' => $role->description, 'permissions' => $permissions,
            ]);
        });

        return redirect()->route('admin.roles.index')->with('status', "Role \"{$role->label()}\" updated.");
    }

    /**
     * Restore a built-in role's default permissions.
     */
    public function reset(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);
        abort_unless($role->builtInRole() !== null, 404);

        $oldPermissions = $role->permissionNames();
        $permissions = $this->permissionsAfterChange($request->user(), $oldPermissions, $role->builtInRole()->defaultPermissions());
        $role->syncPermissions($permissions);
        $this->auditLogger->log('role.permissions_reset', $role, ['permissions' => $oldPermissions], ['permissions' => $permissions]);

        return redirect()->route('admin.roles.edit', $role)->with('status', "\"{$role->label()}\" was reset to its default permissions.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        $this->auditLogger->log('role.deleted', $role, ['name' => $role->name, 'permissions' => $role->permissionNames()]);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', "Role \"{$role->name}\" deleted.");
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function permissionRules(): array
    {
        return [
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(PermissionArea::allPermissions())],
        ];
    }

    /**
     * Permissions the actor may grant or remove: only ones they hold themselves.
     *
     * @return list<string>
     */
    private function grantablePermissions(User $actor): array
    {
        return $actor->isSuperAdmin()
            ? PermissionArea::allPermissions()
            : $actor->getAllPermissions()->pluck('name')->all();
    }

    /**
     * Apply the requested permissions, leaving untouched any the actor is not allowed to grant or remove.
     *
     * @param  list<string>  $currentPermissions
     * @param  list<string>  $requestedPermissions
     * @return list<string>
     */
    private function permissionsAfterChange(User $actor, array $currentPermissions, array $requestedPermissions): array
    {
        $grantable = $this->grantablePermissions($actor);

        return collect($currentPermissions)
            ->diff($grantable)
            ->merge(array_intersect($requestedPermissions, $grantable))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
