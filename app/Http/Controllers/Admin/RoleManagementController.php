<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\Access\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    /**
     * Display the roles and their permission assignments.
     */
    public function index(RoleManagementService $roleManagementService): View
    {
        return view('admin.access.roles.index', [
            'roles' => $roleManagementService->paginate(),
            'systemRoles' => RoleManagementService::SYSTEM_ROLES,
        ]);
    }

    /**
     * Show the create role form.
     */
    public function create(RoleManagementService $roleManagementService): View
    {
        return view('admin.access.roles.create', [
            'permissionGroups' => $roleManagementService->groupedPermissions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(
        StoreRoleRequest $request,
        RoleManagementService $roleManagementService,
    ): RedirectResponse {
        $role = $roleManagementService->create($request);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role created successfully.');
    }

    /**
     * Show the edit role form.
     */
    public function edit(Role $role, RoleManagementService $roleManagementService): View
    {
        return view('admin.access.roles.edit', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $roleManagementService->groupedPermissions(),
            'isSystemRole' => $roleManagementService->isSystemRole($role),
        ]);
    }

    /**
     * Update a role and its permissions.
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role,
        RoleManagementService $roleManagementService,
    ): RedirectResponse {
        $roleManagementService->update($role, $request);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role updated successfully.');
    }
}
