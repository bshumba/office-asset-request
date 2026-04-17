<?php

namespace App\Policies;

use App\Enums\AssetRequestStatusEnum;
use App\Models\AssetRequest;
use App\Models\User;

class AssetRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('requests.view-all')
            || $user->can('requests.view-department')
            || $user->can('requests.view-own');
    }

    public function view(User $user, AssetRequest $assetRequest): bool
    {
        if ($user->can('requests.view-all')) {
            return true;
        }

        if ($user->can('requests.view-department') && $this->sharesDepartment($user, $assetRequest->department_id)) {
            return true;
        }

        return $user->can('requests.view-own') && $assetRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('requests.create');
    }

    public function cancel(User $user, AssetRequest $assetRequest): bool
    {
        return $user->can('requests.cancel-own')
            && $assetRequest->user_id === $user->id
            && $assetRequest->status === AssetRequestStatusEnum::PENDING;
    }

    public function managerApprove(User $user, AssetRequest $assetRequest): bool
    {
        return $user->can('requests.manager-approve')
            && $user->can('requests.view-department')
            && $this->sharesDepartment($user, $assetRequest->department_id)
            && $assetRequest->status === AssetRequestStatusEnum::PENDING;
    }

    public function adminApprove(User $user, AssetRequest $assetRequest): bool
    {
        return $user->can('requests.admin-approve')
            && $assetRequest->status === AssetRequestStatusEnum::MANAGER_APPROVED;
    }

    public function reject(User $user, AssetRequest $assetRequest): bool
    {
        if (! $user->can('requests.reject')) {
            return false;
        }

        if ($user->can('requests.view-all')) {
            return in_array(
                $assetRequest->status,
                [AssetRequestStatusEnum::PENDING, AssetRequestStatusEnum::MANAGER_APPROVED],
                true,
            );
        }

        return $user->can('requests.view-department')
            && $this->sharesDepartment($user, $assetRequest->department_id)
            && $assetRequest->status === AssetRequestStatusEnum::PENDING;
    }

    private function sharesDepartment(User $user, ?int $departmentId): bool
    {
        return $user->department_id !== null
            && $departmentId !== null
            && $user->department_id === $departmentId;
    }
}
