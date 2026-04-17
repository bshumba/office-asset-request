<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetReturnRequest;
use App\Models\AssetIssue;
use App\Models\User;
use App\Services\Inventory\AssetReturnService;
use Illuminate\Http\RedirectResponse;

class AssetReturnController extends Controller
{
    /**
     * Record a return against an issued asset.
     */
    public function store(
        StoreAssetReturnRequest $request,
        AssetIssue $assetIssue,
        AssetReturnService $assetReturnService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $assetReturnService->store($assetIssue, $user, $request);

        return redirect()
            ->route('admin.issues.show', $assetIssue)
            ->with('status', 'Asset return recorded successfully.');
    }
}
