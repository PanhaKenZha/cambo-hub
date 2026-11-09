<?php

namespace App\Models;

use App\Enums\AdminRole;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * An administrator role. Built-in ("system") roles come from {@see AdminRole};
 * custom roles are created on the Roles page.
 */
class Role extends SpatieRole
{
    public function builtInRole(): ?AdminRole
    {
        return AdminRole::tryFrom($this->name);
    }

    public function label(): string
    {
        return $this->builtInRole()?->label() ?? $this->name;
    }

    public function summary(): ?string
    {
        return $this->description ?: $this->builtInRole()?->description();
    }

    public function isSystem(): bool
    {
        return $this->is_system || $this->builtInRole() !== null;
    }

    /**
     * The Super Admin role always has every permission and cannot be changed.
     */
    public function isLocked(): bool
    {
        return $this->builtInRole() === AdminRole::SuperAdmin;
    }

    /**
     * How much access this role has in one area: "full", "manage", "view" or "none".
     */
    public function accessLevel(PermissionArea $area): string
    {
        $granted = array_values(array_intersect($area->permissions(), $this->permissionNames()));

        return match (true) {
            $granted === [] => 'none',
            count($granted) === count($area->permissions()) => 'full',
            $granted === [$area->permission(PermissionAction::View)] => 'view',
            default => 'manage',
        };
    }

    /**
     * Built-in roles in their defined order, followed by custom roles alphabetically.
     *
     * @param  Collection<int, self>  $roles
     * @return Collection<int, self>
     */
    public static function sortForDisplay(Collection $roles): Collection
    {
        $builtInOrder = array_flip(AdminRole::values());

        return $roles->sortBy(fn (self $role): string => isset($builtInOrder[$role->name])
            ? sprintf('0-%03d', $builtInOrder[$role->name])
            : '1-'.mb_strtolower($role->name))->values();
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return $this->permissions->pluck('name')->all();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }
}
