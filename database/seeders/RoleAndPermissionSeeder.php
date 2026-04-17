<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $departmentManager = Role::findOrCreate('Department Manager', 'web');
        $staff = Role::findOrCreate('Staff', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $departmentManager->syncPermissions($this->managerPermissions());
        $staff->syncPermissions($this->staffPermissions());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return list<string>
     */
    private function permissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'departments.view',
            'departments.create',
            'departments.update',
            'departments.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.assign',
            'asset-categories.view',
            'asset-categories.create',
            'asset-categories.update',
            'asset-categories.delete',
            'assets.view',
            'assets.create',
            'assets.update',
            'assets.delete',
            'assets.adjust-stock',
            'requests.create',
            'requests.view-own',
            'requests.view-all',
            'requests.view-department',
            'requests.cancel-own',
            'requests.manager-approve',
            'requests.admin-approve',
            'requests.reject',
            'issues.create',
            'issues.view',
            'returns.create',
            'returns.view',
            'reports.view',
            'reports.export',
            'dashboard.view-admin',
            'dashboard.view-manager',
            'dashboard.view-staff',
            'activity.view',
        ];
    }

    /**
     * @return list<string>
     */
    private function managerPermissions(): array
    {
        return [
            'dashboard.view-manager',
            'requests.view-department',
            'requests.manager-approve',
            'requests.reject',
            'assets.view',
            'issues.view',
            'returns.view',
            'reports.view',
        ];
    }

    /**
     * @return list<string>
     */
    private function staffPermissions(): array
    {
        return [
            'dashboard.view-staff',
            'requests.create',
            'requests.view-own',
            'requests.cancel-own',
            'issues.view',
            'returns.view',
        ];
    }
}
