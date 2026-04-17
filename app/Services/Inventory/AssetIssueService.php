<?php

namespace App\Services\Inventory;

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Http\Requests\Admin\StoreAssetIssueRequest;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AssetRequests\AssetRequestStatusHistoryService;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssetIssueService
{
    public function __construct(
        private readonly AssetRequestStatusHistoryService $assetRequestStatusHistoryService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Paginate all issue records for admin review.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return AssetIssue::query()
            ->with(['asset', 'assetRequest', 'department', 'issuedToUser'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Issue an approved asset request.
     */
    public function issue(
        AssetRequest $assetRequest,
        User $admin,
        StoreAssetIssueRequest $request,
    ): AssetIssue {
        if ($assetRequest->status !== AssetRequestStatusEnum::ADMIN_APPROVED) {
            throw new AccessDeniedHttpException('Only admin-approved requests may be issued.');
        }

        if ($assetRequest->issue()->exists()) {
            throw new AccessDeniedHttpException('This request has already been issued.');
        }

        /** @var AssetIssue $assetIssue */
        $assetIssue = DB::transaction(function () use ($assetRequest, $admin, $request): AssetIssue {
            $data = $request->issueData();
            $asset = $assetRequest->asset()->lockForUpdate()->firstOrFail();

            $asset->decrement('quantity_available', $data['quantity_issued']);

            $assetRequest->forceFill([
                'status' => AssetRequestStatusEnum::ISSUED,
            ])->save();

            $assetIssue = AssetIssue::query()->create([
                'asset_request_id' => $assetRequest->id,
                'asset_id' => $asset->id,
                'issued_to_user_id' => $assetRequest->user_id,
                'issued_by_user_id' => $admin->id,
                'department_id' => $assetRequest->department_id,
                'quantity_issued' => $data['quantity_issued'],
                'issued_at' => now(),
                'expected_return_date' => $data['expected_return_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => AssetIssueStatusEnum::ISSUED,
            ]);

            $this->assetRequestStatusHistoryService->record(
                assetRequest: $assetRequest,
                status: AssetRequestStatusEnum::ISSUED,
                actedBy: $admin->id,
                comment: $data['notes'] ?? 'Asset issued by admin.',
            );

            $this->notificationService->notify(
                recipients: $assetRequest->user,
                type: 'request.issued',
                title: 'An asset was issued to you',
                message: $assetRequest->request_number.' was issued. Check your assigned assets workspace for return details and timing.',
                actionUrl: route('staff.assigned-assets.show', $assetIssue),
                resourceType: NotificationLog::RESOURCE_ASSET_ISSUE,
                resourceId: $assetIssue->id,
            );

            return $assetIssue;
        });

        return $assetIssue->fresh([
            'asset',
            'assetRequest',
            'department',
            'issuedToUser',
            'returns.receivedByUser',
        ]) ?? $assetIssue;
    }
}
