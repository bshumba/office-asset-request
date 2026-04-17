<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedAssetRequest;
use App\Http\Requests\Admin\UpdateManagedAssetRequest;
use App\Models\Asset;
use App\Services\Inventory\AssetManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetController extends Controller
{
    /**
     * Display the asset workspace.
     */
    public function index(AssetManagementService $assetManagementService): View
    {
        return view('admin.assets.index', [
            'assets' => $assetManagementService->paginate(),
        ]);
    }

    /**
     * Show the asset creation form.
     */
    public function create(AssetManagementService $assetManagementService): View
    {
        return view('admin.assets.create', [
            'categories' => $assetManagementService->categories(),
            'departments' => $assetManagementService->departments(),
            'statuses' => AssetStatusEnum::cases(),
            'unitTypes' => AssetUnitTypeEnum::cases(),
        ]);
    }

    /**
     * Store a new asset record.
     */
    public function store(
        StoreManagedAssetRequest $request,
        AssetManagementService $assetManagementService,
    ): RedirectResponse {
        $asset = $assetManagementService->create($request);

        return redirect()
            ->route('admin.assets.edit', $asset)
            ->with('status', $asset->name.' was created successfully.');
    }

    /**
     * Show the asset edit form.
     */
    public function edit(Asset $asset, AssetManagementService $assetManagementService): View
    {
        return view('admin.assets.edit', [
            'asset' => $asset->load(['category', 'department']),
            'categories' => $assetManagementService->categories(),
            'departments' => $assetManagementService->departments(),
            'statuses' => AssetStatusEnum::cases(),
            'unitTypes' => AssetUnitTypeEnum::cases(),
        ]);
    }

    /**
     * Update an asset record.
     */
    public function update(
        UpdateManagedAssetRequest $request,
        Asset $asset,
        AssetManagementService $assetManagementService,
    ): RedirectResponse {
        $updatedAsset = $assetManagementService->update($asset, $request);

        return redirect()
            ->route('admin.assets.edit', $updatedAsset)
            ->with('status', $updatedAsset->name.' was updated successfully.');
    }

    /**
     * Delete an asset record.
     */
    public function destroy(
        Asset $asset,
        AssetManagementService $assetManagementService,
    ): RedirectResponse {
        $assetName = $asset->name;
        $assetManagementService->delete($asset);

        return redirect()
            ->route('admin.assets.index')
            ->with('status', $assetName.' was deleted.');
    }
}
