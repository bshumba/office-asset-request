<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRequest;
use App\Models\User;
use App\Services\Users\AdminUserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display existing user accounts.
     */
    public function index(AdminUserManagementService $adminUserManagementService): View
    {
        return view('admin.users.index', [
            'users' => $adminUserManagementService->paginate(),
        ]);
    }

    /**
     * Show the staff and manager creation form.
     */
    public function create(AdminUserManagementService $adminUserManagementService): View
    {
        return view('admin.users.create', [
            'departments' => $adminUserManagementService->activeDepartments(),
            'roles' => ['Department Manager', 'Staff'],
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    /**
     * Store a new staff or manager account.
     */
    public function store(
        StoreManagedUserRequest $request,
        AdminUserManagementService $adminUserManagementService,
    ): RedirectResponse {
        $user = $adminUserManagementService->create($request);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $user->name.' was created successfully.');
    }

    /**
     * Show the edit form for a managed account.
     */
    public function edit(User $managedUser, AdminUserManagementService $adminUserManagementService): View
    {
        return view('admin.users.edit', [
            'managedUser' => $managedUser->load(['department', 'roles']),
            'departments' => $adminUserManagementService->manageableDepartments(),
            'roles' => ['Department Manager', 'Staff'],
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    /**
     * Update a managed account.
     */
    public function update(
        UpdateManagedUserRequest $request,
        User $managedUser,
        AdminUserManagementService $adminUserManagementService,
    ): RedirectResponse {
        $user = $adminUserManagementService->update($managedUser, $request);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', $user->name.' was updated successfully.');
    }

    /**
     * Deactivate a managed account.
     */
    public function deactivate(
        Request $request,
        User $managedUser,
        AdminUserManagementService $adminUserManagementService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $deactivatedUser = $adminUserManagementService->deactivate($managedUser, $user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $deactivatedUser->name.' was deactivated.');
    }
}
