<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Services\Access\PermissionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionManagementController extends Controller
{
    /**
     * Display permissions and the creation form.
     */
    public function index(PermissionManagementService $permissionManagementService): View
    {
        return view('admin.access.permissions.index', [
            'permissions' => $permissionManagementService->paginate(),
            'roles' => $permissionManagementService->roles(),
        ]);
    }

    /**
     * Store a new permission and optionally assign it to roles.
     */
    public function store(
        StorePermissionRequest $request,
        PermissionManagementService $permissionManagementService,
    ): RedirectResponse {
        $permissionManagementService->create($request);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission created successfully.');
    }
}
