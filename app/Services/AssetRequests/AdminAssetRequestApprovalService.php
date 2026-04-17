<?php

namespace App\Services\AssetRequests;

use App\Enums\AssetRequestStatusEnum;
use App\Http\Requests\Admin\ApproveAssetRequestRequest;
use App\Http\Requests\Admin\RejectAssetRequestRequest;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdminAssetRequestApprovalService
{
    public function __construct(
        private readonly AssetRequestStatusHistoryService $assetRequestStatusHistoryService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Paginate all request records for admin review.
     */
    public function paginateForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return AssetRequest::query()
            ->with(['asset.category', 'department', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Approve a manager-approved request for issuing.
     */
    public function approve(
        AssetRequest $assetRequest,
        User $admin,
        ApproveAssetRequestRequest $request,
    ): AssetRequest {
        if ($assetRequest->status !== AssetRequestStatusEnum::MANAGER_APPROVED) {
            throw new AccessDeniedHttpException('Only manager-approved requests may be approved by admin.');
        }

        DB::transaction(function () use ($assetRequest, $admin, $request): void {
            $data = $request->approvalData();

            $assetRequest->forceFill([
                'quantity_approved' => $data['quantity_approved'],
                'status' => AssetRequestStatusEnum::ADMIN_APPROVED,
                'admin_reviewed_by' => $admin->id,
                'admin_reviewed_at' => now(),
                'admin_comment' => $data['admin_comment'] ?? null,
                'rejection_reason' => null,
            ])->save();

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::ADMIN_APPROVED,
                actedBy: $admin->id,
                comment: $data['admin_comment'] ?? 'Approved by admin.',
            );

            $this->notificationService->notify(
                recipients: $assetRequest->user,
                type: 'request.admin-approved',
                title: 'Your request passed admin approval',
                message: $assetRequest->request_number.' was approved by admin and is now waiting to be issued.',
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
            'issue',
        ]) ?? $assetRequest;
    }

    /**
     * Reject a request during admin review.
     */
    public function reject(
        AssetRequest $assetRequest,
        User $admin,
        RejectAssetRequestRequest $request,
    ): AssetRequest {
        if ($assetRequest->status !== AssetRequestStatusEnum::MANAGER_APPROVED) {
            throw new AccessDeniedHttpException('Only manager-approved requests may be rejected by admin.');
        }

        DB::transaction(function () use ($assetRequest, $admin, $request): void {
            $data = $request->rejectionData();

            $assetRequest->forceFill([
                'quantity_approved' => null,
                'status' => AssetRequestStatusEnum::REJECTED,
                'admin_reviewed_by' => $admin->id,
                'admin_reviewed_at' => now(),
                'admin_comment' => $data['admin_comment'] ?? null,
                'rejection_reason' => $data['rejection_reason'],
            ])->save();

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::REJECTED,
                actedBy: $admin->id,
                comment: $data['admin_comment'] ?? $data['rejection_reason'],
            );

            $this->notificationService->notify(
                recipients: $assetRequest->user,
                type: 'request.rejected',
                title: 'Your request was rejected',
                message: $assetRequest->request_number.' was rejected during admin review. Reason: '.$data['rejection_reason'],
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
            'issue',
        ]) ?? $assetRequest;
    }
}
