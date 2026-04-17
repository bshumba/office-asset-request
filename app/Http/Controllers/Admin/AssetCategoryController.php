<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetCategoryRequest;
use App\Http\Requests\Admin\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use App\Services\Inventory\AssetCategoryManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    /**
     * Display the asset category workspace.
     */
    public function index(AssetCategoryManagementService $assetCategoryManagementService): View
    {
        return view('admin.asset-categories.index', [
            'categories' => $assetCategoryManagementService->paginate(),
        ]);
    }

    /**
     * Show the category creation form.
     */
    public function create(): View
    {
        return view('admin.asset-categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(
        StoreAssetCategoryRequest $request,
        AssetCategoryManagementService $assetCategoryManagementService,
    ): RedirectResponse {
        $category = $assetCategoryManagementService->create($request);

        return redirect()
            ->route('admin.asset-categories.edit', $category)
            ->with('status', $category->name.' was created successfully.');
    }

    /**
     * Show the category edit form.
     */
    public function edit(AssetCategory $assetCategory): View
    {
        return view('admin.asset-categories.edit', [
            'assetCategory' => $assetCategory,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(
        UpdateAssetCategoryRequest $request,
        AssetCategory $assetCategory,
        AssetCategoryManagementService $assetCategoryManagementService,
    ): RedirectResponse {
        $updatedCategory = $assetCategoryManagementService->update($assetCategory, $request);

        return redirect()
            ->route('admin.asset-categories.edit', $updatedCategory)
            ->with('status', $updatedCategory->name.' was updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroy(
        AssetCategory $assetCategory,
        AssetCategoryManagementService $assetCategoryManagementService,
    ): RedirectResponse {
        $categoryName = $assetCategory->name;
        $assetCategoryManagementService->delete($assetCategory);

        return redirect()
            ->route('admin.asset-categories.index')
            ->with('status', $categoryName.' was deleted.');
    }
}
