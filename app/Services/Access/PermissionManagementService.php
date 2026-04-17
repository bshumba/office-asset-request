<?php

namespace App\Services\Access;

use App\Http\Requests\Admin\StorePermissionRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionManagementService
{
    /**
     * Paginate permissions with their assigned roles.
     */
    public function paginate(int $perPage = 14): LengthAwarePaginator
    {
        return Permission::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all roles that can receive permissions.
     *
     * @return Collection<int, Role>
     */
    public function roles(): Collection
    {
        return Role::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a permission and optionally assign it to selected roles.
     */
    public function create(StorePermissionRequest $request): Permission
    {
        $data = $request->permissionData();

        $permission = Permission::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($data['roles'])) {
            $roles = Role::query()
                ->whereIn('id', $data['roles'])
                ->get();

            $roles->each(function (Role $role) use ($permission): void {
                $role->givePermissionTo($permission);
            });
        }

        $this->flushPermissionCache();

        return $permission->load('roles');
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
