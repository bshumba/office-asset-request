<?php

namespace App\Services\AssetRequests;

use App\Enums\AssetRequestStatusEnum;
use App\Models\AssetRequest;
use App\Models\AssetRequestStatusHistory;

class AssetRequestStatusHistoryService
{
    /**
     * Write a request status history row.
     */
    public function record(
        AssetRequest $assetRequest,
        AssetRequestStatusEnum $status,
        ?int $actedBy,
        ?string $comment = null,
    ): AssetRequestStatusHistory {
        return AssetRequestStatusHistory::query()->create([
            'asset_request_id' => $assetRequest->id,
            'status' => $status,
            'comment' => $comment,
            'acted_by' => $actedBy,
            'created_at' => now(),
        ]);
    }
}
