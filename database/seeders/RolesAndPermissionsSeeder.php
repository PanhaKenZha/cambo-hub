<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\PermissionArea;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every permission and built-in role. Safe to run repeatedly:
 * permission changes made on the Roles page are kept.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = PermissionArea::allPermissions();

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // Remove permissions that no longer exist in the catalogue.
        Permission::query()->where('guard_name', 'web')->whereNotIn('name', $permissionNames)->delete();

        // DatabaseSeeder disables model events, so permission writes do not invalidate the cache.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AdminRole::cases() as $adminRole) {
            $role = Role::findOrCreate($adminRole->value, 'web');

            if (! $role->is_system) {
                $role->forceFill(['is_system' => true])->save();
            }

            // Defaults are only applied to new roles (or roles left with no permissions),
            // so changes made in the admin panel survive re-seeding. Super Admin always gets everything.
            if ($role->wasRecentlyCreated || $adminRole === AdminRole::SuperAdmin || $role->permissions()->doesntExist()) {
                $role->syncPermissions($adminRole->defaultPermissions());
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
