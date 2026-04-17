<?php

namespace App\Services\Inventory;

use App\Models\AssetIssue;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffAssignedAssetService
{
    /**
     * Paginate assets currently or previously assigned to a staff user.
     */
    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return AssetIssue::query()
            ->with(['asset', 'assetRequest', 'department', 'returns'])
            ->where('issued_to_user_id', $user->id)
            ->latest('issued_at')
            ->paginate($perPage);
    }
}
