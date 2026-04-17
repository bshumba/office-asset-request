<?php

namespace App\Services\Users;

use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminUserManagementService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Paginate the user list for admin review.
     */
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return User::query()
            ->with(['department', 'roles'])
            ->whereDoesntHave('roles', function ($query): void {
                $query->where('name', 'Super Admin');
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get active departments for the user creation form.
     *
     * @return Collection<int, Department>
     */
    public function activeDepartments(): Collection
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get departments for edit and reassignment workflows.
     *
     * @return Collection<int, Department>
     */
    public function manageableDepartments(): Collection
    {
        return Department::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new staff or manager account from the admin workspace.
     */
    public function create(StoreManagedUserRequest $request): User
    {
        /** @var User $user */
        $user = DB::transaction(function () use ($request): User {
            $data = $request->userData();

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'],
                'password' => $data['password'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $user->syncRoles([$data['role']]);

            $this->syncDepartmentManagerAssignment($user, $data['role'], $data['department_id']);

            $this->notificationService->notify(
                recipients: $user,
                type: 'account.created',
                title: 'Your account is ready',
                message: 'An admin created your '.$data['role'].' account. You can now sign in and start using the system.',
                actionUrl: route('dashboard'),
                resourceType: null,
                resourceId: null,
            );

            return $user;
        });

        return $user->fresh(['department', 'roles']) ?? $user;
    }

    /**
     * Update a managed staff or manager account.
     */
    public function update(User $managedUser, UpdateManagedUserRequest $request): User
    {
        /** @var User $user */
        $user = DB::transaction(function () use ($managedUser, $request): User {
            $data = $request->userData();

            $managedUser->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['password'])) {
                $managedUser->password = $data['password'];
            }

            $managedUser->save();
            $managedUser->syncRoles([$data['role']]);

            $this->syncDepartmentManagerAssignment($managedUser, $data['role'], $data['department_id']);

            $this->notificationService->notify(
                recipients: $managedUser,
                type: 'account.updated',
                title: 'Your account was updated',
                message: 'An administrator updated your account details and access settings.',
                actionUrl: route('profile.edit'),
            );

            return $managedUser;
        });

        return $user->fresh(['department', 'roles']) ?? $user;
    }

    /**
     * Deactivate a managed user account.
     */
    public function deactivate(User $managedUser, User $actor): User
    {
        if ($managedUser->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot deactivate your own account from this screen.',
            ]);
        }

        $managedUser->forceFill([
            'status' => 'inactive',
        ])->save();

        $this->releaseManagerSlot($managedUser);

        return $managedUser->fresh(['department', 'roles']) ?? $managedUser;
    }

    /**
     * Assign or clear the department manager slot for a user.
     */
    private function syncDepartmentManagerAssignment(User $user, string $role, int $departmentId): void
    {
        $this->releaseManagerSlot($user);

        if ($role !== 'Department Manager') {
            return;
        }

        $department = Department::query()->findOrFail($departmentId);

        $department->forceFill([
            'manager_user_id' => $user->id,
        ])->save();
    }

    /**
     * Remove any manager slot currently linked to the given user.
     */
    private function releaseManagerSlot(User $user): void
    {
        Department::query()
            ->where('manager_user_id', $user->id)
            ->update([
                'manager_user_id' => null,
            ]);
    }
}
