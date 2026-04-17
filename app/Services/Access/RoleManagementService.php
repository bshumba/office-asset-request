<?php

namespace App\Services\Access;

use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementService
{
    public const SYSTEM_ROLES = [
        'Super Admin',
        'Department Manager',
        'Staff',
    ];

    /**
     * Paginate roles with their permissions.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get permissions grouped by their namespace prefix.
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    public function groupedPermissions(): Collection
    {
        /** @var Collection<string, Collection<int, Permission>> $grouped */
        $grouped = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => Str::before($permission->name, '.'));

        return $grouped;
    }

    /**
     * Create a new role and attach the selected permissions.
     */
    public function create(StoreRoleRequest $request): Role
    {
        $data = $request->roleData();

        $role = Role::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        $this->flushPermissionCache();

        return $role->load('permissions');
    }

    /**
     * Update a role and sync its permissions.
     */
    public function update(Role $role, UpdateRoleRequest $request): Role
    {
        $data = $request->roleData();

        if (! $this->isSystemRole($role)) {
            $role->forceFill([
                'name' => $data['name'],
            ])->save();
        }

        $role->syncPermissions($data['permissions'] ?? []);

        $this->flushPermissionCache();

        return $role->fresh('permissions') ?? $role;
    }

    /**
     * Determine whether the given role is part of the system workflow.
     */
    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLES, true);
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
