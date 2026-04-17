<?php

namespace App\Services\Inventory;

use App\Http\Requests\Admin\StoreManagedAssetRequest;
use App\Http\Requests\Admin\UpdateManagedAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssetManagementService
{
    /**
     * Paginate asset records with related data.
     */
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Asset::query()
            ->with(['category', 'department'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get categories for the asset forms.
     *
     * @return Collection<int, AssetCategory>
     */
    public function categories(): Collection
    {
        return AssetCategory::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get departments for the asset forms.
     *
     * @return Collection<int, Department>
     */
    public function departments(): Collection
    {
        return Department::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create an asset record.
     */
    public function create(StoreManagedAssetRequest $request): Asset
    {
        $data = $request->assetData();

        $asset = Asset::query()->create([
            ...$data,
            'slug' => Str::slug($data['name'].'-'.$data['asset_code']),
        ]);

        return $asset->fresh(['category', 'department']) ?? $asset;
    }

    /**
     * Update an asset record.
     */
    public function update(Asset $asset, UpdateManagedAssetRequest $request): Asset
    {
        $data = $request->assetData();

        $asset->forceFill([
            ...$data,
            'slug' => Str::slug($data['name'].'-'.$data['asset_code']),
        ])->save();

        return $asset->fresh(['category', 'department']) ?? $asset;
    }

    /**
     * Delete an asset when it has no workflow history attached.
     */
    public function delete(Asset $asset): void
    {
        if (
            $asset->assetRequests()->exists()
            || $asset->assetIssues()->exists()
            || $asset->adjustments()->exists()
        ) {
            throw ValidationException::withMessages([
                'asset' => 'This asset already has workflow history. Set it inactive instead of deleting it.',
            ]);
        }

        $asset->delete();
    }
}
