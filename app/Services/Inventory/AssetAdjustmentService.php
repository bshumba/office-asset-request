<?php

namespace App\Services\Inventory;

use App\Enums\StockAdjustmentTypeEnum;
use App\Http\Requests\Admin\StoreAssetAdjustmentRequest;
use App\Models\Asset;
use App\Models\AssetAdjustment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssetAdjustmentService
{
    /**
     * Paginate stock adjustments for the admin workspace.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return AssetAdjustment::query()
            ->with(['asset', 'createdBy'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Store a stock adjustment and update the asset balances.
     */
    public function store(User $admin, StoreAssetAdjustmentRequest $request): AssetAdjustment
    {
        /** @var AssetAdjustment $adjustment */
        $adjustment = DB::transaction(function () use ($admin, $request): AssetAdjustment {
            $data = $request->adjustmentData();

            /** @var Asset $asset */
            $asset = Asset::query()
                ->lockForUpdate()
                ->findOrFail($data['asset_id']);

            $direction = $data['type'] === StockAdjustmentTypeEnum::INCREASE->value ? 1 : -1;
            $delta = $direction * $data['quantity'];

            $asset->forceFill([
                'quantity_total' => $asset->quantity_total + $delta,
                'quantity_available' => $asset->quantity_available + $delta,
            ])->save();

            return AssetAdjustment::query()->create([
                'asset_id' => $asset->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $admin->id,
            ]);
        });

        return $adjustment->fresh([
            'asset',
            'createdBy',
        ]) ?? $adjustment;
    }
}
