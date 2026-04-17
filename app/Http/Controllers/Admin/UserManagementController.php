<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Services\Users\AdminUserManagementService;
use Illuminate\Http\RedirectResponse;
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
}
