<?php

namespace App\Policies;

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\User;

class AssetIssuePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('issues.view');
    }

    public function view(User $user, AssetIssue $assetIssue): bool
    {
        if (! $user->can('issues.view')) {
            return false;
        }

        if ($user->can('requests.view-all')) {
            return true;
        }

        if ($user->can('requests.view-department') && $this->sharesDepartment($user, $assetIssue->department_id)) {
            return true;
        }

        return $assetIssue->issued_to_user_id === $user->id;
    }

    public function create(User $user, AssetRequest $assetRequest): bool
    {
        return $user->can('issues.create')
            && $assetRequest->status === AssetRequestStatusEnum::ADMIN_APPROVED;
    }

    public function createReturn(User $user, AssetIssue $assetIssue): bool
    {
        if (! $user->can('returns.create')) {
            return false;
        }

        $isReturnable = in_array(
            $assetIssue->status,
            [AssetIssueStatusEnum::ISSUED, AssetIssueStatusEnum::PARTIALLY_RETURNED],
            true,
        );

        if (! $isReturnable) {
            return false;
        }

        if ($user->can('requests.view-all')) {
            return true;
        }

        return $user->can('requests.view-department')
            && $this->sharesDepartment($user, $assetIssue->department_id);
    }

    private function sharesDepartment(User $user, ?int $departmentId): bool
    {
        return $user->department_id !== null
            && $departmentId !== null
            && $user->department_id === $departmentId;
    }
}
