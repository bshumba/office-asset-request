<?php

namespace App\Services\Users;

use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Models\Department;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

            if ($data['role'] === 'Department Manager') {
                $department = Department::query()->findOrFail($data['department_id']);

                if ($department->manager_user_id === null) {
                    $department->forceFill([
                        'manager_user_id' => $user->id,
                    ])->save();
                }
            }

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
}
