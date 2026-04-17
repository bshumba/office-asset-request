<?php

namespace App\Services\AssetRequests;

use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Http\Requests\Staff\StoreAssetRequestRequest;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffAssetRequestService
{
    public function __construct(
        private readonly AssetRequestStatusHistoryService $assetRequestStatusHistoryService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Get request records belonging to the authenticated staff user.
     */
    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return AssetRequest::query()
            ->with(['asset.category', 'department'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get the active assets that a staff user may request.
     *
     * @return Collection<int, Asset>
     */
    public function availableAssetsForUser(User $user): Collection
    {
        return Asset::query()
            ->with('category')
            ->where('status', AssetStatusEnum::ACTIVE)
            ->where(function ($query) use ($user): void {
                if ($user->department_id !== null) {
                    $query->where('department_id', $user->department_id)
                        ->orWhereNull('department_id');

                    return;
                }

                $query->whereNull('department_id');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new staff asset request and its first history record.
     *
     * @throws ValidationException
     */
    public function create(User $user, StoreAssetRequestRequest $request): AssetRequest
    {
        if ($user->department_id === null) {
            throw ValidationException::withMessages([
                'asset_id' => 'Your account must belong to a department before you can submit requests.',
            ]);
        }

        /** @var AssetRequest $assetRequest */
        $assetRequest = DB::transaction(function () use ($request, $user): AssetRequest {
            $data = $request->workflowData();

            $assetRequest = AssetRequest::query()->create([
                'request_number' => $this->generateRequestNumber(),
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'asset_id' => $data['asset_id'],
                'quantity_requested' => $data['quantity_requested'],
                'reason' => $data['reason'],
                'needed_by_date' => $data['needed_by_date'] ?? null,
                'priority' => $data['priority'],
                'status' => AssetRequestStatusEnum::PENDING,
            ]);

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::PENDING,
                actedBy: $user->id,
                comment: 'Request submitted by staff.',
            );

            $managers = User::query()
                ->role('Department Manager')
                ->where('department_id', $user->department_id)
                ->get();

            $this->notificationService->notify(
                recipients: $managers,
                type: 'request.submitted',
                title: 'New asset request submitted',
                message: $user->name.' submitted '.$assetRequest->request_number.' for '.$assetRequest->quantity_requested.' item(s).',
                actionUrl: route('manager.requests.show', $assetRequest),
                resourceType: NotificationLog::RESOURCE_ASSET_REQUEST,
                resourceId: $assetRequest->id,
            );

            return $assetRequest;
        });

        return $assetRequest->fresh(['asset.category', 'department']) ?? $assetRequest;
    }

    /**
     * Cancel a pending request and write its status history entry.
     */
    public function cancel(AssetRequest $assetRequest, User $user): AssetRequest
    {
        DB::transaction(function () use ($assetRequest, $user): void {
            $assetRequest->forceFill([
                'status' => AssetRequestStatusEnum::CANCELLED,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ])->save();

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::CANCELLED,
                actedBy: $user->id,
                comment: 'Request cancelled by requester.',
            );
        });

        return $assetRequest->fresh(['asset.category', 'department']) ?? $assetRequest;
    }

    /**
     * Generate the next request number for the current day.
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'REQ-'.now()->format('Ymd').'-';

        $latestRequestNumber = AssetRequest::query()
            ->where('request_number', 'like', $prefix.'%')
            ->latest('id')
            ->value('request_number');

        $sequence = 1;

        if (is_string($latestRequestNumber)) {
            $lastSequence = (int) substr($latestRequestNumber, -4);
            $sequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
