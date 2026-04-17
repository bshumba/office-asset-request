<?php

namespace App\Services\Inventory;

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Http\Requests\Admin\StoreAssetReturnRequest;
use App\Models\AssetIssue;
use App\Models\NotificationLog;
use App\Models\AssetReturn;
use App\Models\User;
use App\Services\AssetRequests\AssetRequestStatusHistoryService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssetReturnService
{
    public function __construct(
        private readonly AssetRequestStatusHistoryService $assetRequestStatusHistoryService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Record an asset return and update stock and statuses.
     */
    public function store(
        AssetIssue $assetIssue,
        User $admin,
        StoreAssetReturnRequest $request,
    ): AssetReturn {
        if (! in_array($assetIssue->status, [AssetIssueStatusEnum::ISSUED, AssetIssueStatusEnum::PARTIALLY_RETURNED], true)) {
            throw new AccessDeniedHttpException('Only open issue records may receive returns.');
        }

        /** @var AssetReturn $assetReturn */
        $assetReturn = DB::transaction(function () use ($assetIssue, $admin, $request): AssetReturn {
            $data = $request->returnData();

            $assetIssue = $assetIssue->loadMissing('assetRequest');
            $asset = $assetIssue->asset()->lockForUpdate()->firstOrFail();

            $assetReturn = AssetReturn::query()->create([
                'asset_issue_id' => $assetIssue->id,
                'received_by_user_id' => $admin->id,
                'quantity_returned' => $data['quantity_returned'],
                'condition_on_return' => $data['condition_on_return'],
                'remarks' => $data['remarks'] ?? null,
                'returned_at' => now(),
            ]);

            $asset->increment('quantity_available', $data['quantity_returned']);

            $returnedQuantity = (int) $assetIssue->returns()->sum('quantity_returned');
            $outstandingQuantity = $assetIssue->quantity_issued - $returnedQuantity;

            if ($outstandingQuantity <= 0) {
                $assetIssue->forceFill([
                    'status' => AssetIssueStatusEnum::RETURNED,
                ])->save();

                $assetIssue->assetRequest?->forceFill([
                    'status' => AssetRequestStatusEnum::RETURNED,
                ])->save();

                if ($assetIssue->assetRequest) {
                    $this->assetRequestStatusHistoryService->record(
                        assetRequest: $assetIssue->assetRequest,
                        status: AssetRequestStatusEnum::RETURNED,
                        actedBy: $admin->id,
                        comment: $data['remarks'] ?? 'Asset fully returned.',
                    );
                }

                $this->notificationService->notify(
                    recipients: $assetIssue->issuedToUser,
                    type: 'request.returned',
                    title: 'A return was completed',
                    message: 'The issued asset linked to '.$assetIssue->assetRequest?->request_number.' was marked as fully returned.',
                    actionUrl: route('staff.assigned-assets.show', $assetIssue),
                    resourceType: NotificationLog::RESOURCE_ASSET_ISSUE,
                    resourceId: $assetIssue->id,
                );
            } else {
                $assetIssue->forceFill([
                    'status' => AssetIssueStatusEnum::PARTIALLY_RETURNED,
                ])->save();

                $this->notificationService->notify(
                    recipients: $assetIssue->issuedToUser,
                    type: 'request.partially-returned',
                    title: 'A partial return was recorded',
                    message: 'A partial return was recorded for '.$assetIssue->assetRequest?->request_number.'. Outstanding quantity: '.$outstandingQuantity.'.',
                    actionUrl: route('staff.assigned-assets.show', $assetIssue),
                    resourceType: NotificationLog::RESOURCE_ASSET_ISSUE,
                    resourceId: $assetIssue->id,
                );
            }

            return $assetReturn;
        });

        return $assetReturn->fresh([
            'assetIssue.asset',
            'assetIssue.assetRequest',
            'receivedByUser',
        ]) ?? $assetReturn;
    }
}
