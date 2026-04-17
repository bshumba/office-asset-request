<?php

namespace App\Services\AssetRequests;

use App\Enums\AssetRequestStatusEnum;
use App\Http\Requests\Manager\ApproveAssetRequestRequest;
use App\Http\Requests\Manager\RejectAssetRequestRequest;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ManagerAssetRequestReviewService
{
    public function __construct(
        private readonly AssetRequestStatusHistoryService $assetRequestStatusHistoryService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Paginate request records belonging to the manager's department.
     */
    public function paginateForManager(User $manager, int $perPage = 10): LengthAwarePaginator
    {
        return AssetRequest::query()
            ->with(['asset.category', 'user', 'department'])
            ->where('department_id', $manager->department_id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Approve a pending department request.
     */
    public function approve(
        AssetRequest $assetRequest,
        User $manager,
        ApproveAssetRequestRequest $request,
    ): AssetRequest {
        DB::transaction(function () use ($assetRequest, $manager, $request): void {
            $data = $request->reviewData();

            $assetRequest->forceFill([
                'quantity_approved' => $data['quantity_approved'],
                'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
                'manager_reviewed_by' => $manager->id,
                'manager_reviewed_at' => now(),
                'manager_comment' => $data['manager_comment'] ?? null,
                'rejection_reason' => null,
            ])->save();

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::MANAGER_APPROVED,
                actedBy: $manager->id,
                comment: $data['manager_comment'] ?? 'Approved by manager.',
            );

            $admins = User::query()->role('Super Admin')->get();

            $this->notificationService->notify(
                recipients: $assetRequest->user,
                type: 'request.manager-approved',
                title: 'Your request passed manager review',
                message: $assetRequest->request_number.' was approved by your department manager and is waiting for final admin review.',
                actionUrl: route('staff.requests.show', $assetRequest),
                resourceType: NotificationLog::RESOURCE_ASSET_REQUEST,
                resourceId: $assetRequest->id,
            );

            $this->notificationService->notify(
                recipients: $admins,
                type: 'request.ready-for-admin',
                title: 'A request is ready for admin review',
                message: $assetRequest->request_number.' has been approved by a manager and is ready for final approval.',
                actionUrl: route('admin.requests.show', $assetRequest),
                resourceType: NotificationLog::RESOURCE_ASSET_REQUEST,
                resourceId: $assetRequest->id,
            );
        });

        return $assetRequest->fresh([
            'asset.category',
            'department',
            'statusHistories.actor',
            'user',
        ]) ?? $assetRequest;
    }

    /**
     * Reject a pending department request.
     */
    public function reject(
        AssetRequest $assetRequest,
        User $manager,
        RejectAssetRequestRequest $request,
    ): AssetRequest {
        DB::transaction(function () use ($assetRequest, $manager, $request): void {
            $data = $request->reviewData();

            $assetRequest->forceFill([
                'quantity_approved' => null,
                'status' => AssetRequestStatusEnum::REJECTED,
                'manager_reviewed_by' => $manager->id,
                'manager_reviewed_at' => now(),
                'manager_comment' => $data['manager_comment'] ?? null,
                'rejection_reason' => $data['rejection_reason'],
            ])->save();

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::REJECTED,
                actedBy: $manager->id,
                comment: $data['manager_comment'] ?? $data['rejection_reason'],
            );

            $this->notificationService->notify(
                recipients: $assetRequest->user,
                type: 'request.rejected',
                title: 'Your request was rejected',
                message: $assetRequest->request_number.' was rejected during manager review. Reason: '.$data['rejection_reason'],
                actionUrl: route('staff.requests.show', $assetRequest),
                resourceType: NotificationLog::RESOURCE_ASSET_REQUEST,
                resourceId: $assetRequest->id,
            );
        });

        return $assetRequest->fresh([
            'asset.category',
            'department',
            'statusHistories.actor',
            'user',
        ]) ?? $assetRequest;
    }
}
