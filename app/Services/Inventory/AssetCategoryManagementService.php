<?php

namespace App\Services\Inventory;

use App\Http\Requests\Admin\StoreAssetCategoryRequest;
use App\Http\Requests\Admin\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssetCategoryManagementService
{
    /**
     * Paginate categories with asset usage counts.
     */
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return AssetCategory::query()
            ->withCount('assets')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get category options.
     *
     * @return Collection<int, AssetCategory>
     */
    public function options(): Collection
    {
        return AssetCategory::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create an asset category.
     */
    public function create(StoreAssetCategoryRequest $request): AssetCategory
    {
        $data = $request->categoryData();

        $category = AssetCategory::query()->create([
            ...$data,
            'slug' => Str::slug($data['name']),
        ]);

        return $category;
    }

    /**
     * Update an asset category.
     */
    public function update(AssetCategory $assetCategory, UpdateAssetCategoryRequest $request): AssetCategory
    {
        $data = $request->categoryData();

        $assetCategory->forceFill([
            ...$data,
            'slug' => Str::slug($data['name']),
        ])->save();

        return $assetCategory->fresh() ?? $assetCategory;
    }

    /**
     * Delete an asset category when it is not in use.
     */
    public function delete(AssetCategory $assetCategory): void
    {
        if ($assetCategory->assets()->exists()) {
            throw ValidationException::withMessages([
                'asset_category' => 'This category is already attached to assets. Set it inactive instead of deleting it.',
            ]);
        }

        $assetCategory->delete();
    }
}
