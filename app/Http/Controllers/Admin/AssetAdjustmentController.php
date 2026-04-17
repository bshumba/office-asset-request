<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockAdjustmentReasonEnum;
use App\Enums\StockAdjustmentTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetAdjustmentRequest;
use App\Models\Asset;
use App\Models\User;
use App\Services\Inventory\AssetAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetAdjustmentController extends Controller
{
    /**
     * Display the stock adjustment workspace.
     */
    public function index(AssetAdjustmentService $assetAdjustmentService): View
    {
        return view('admin.adjustments.index', [
            'adjustments' => $assetAdjustmentService->paginate(),
            'assets' => Asset::query()->orderBy('name')->get(),
            'reasons' => StockAdjustmentReasonEnum::cases(),
            'types' => StockAdjustmentTypeEnum::cases(),
        ]);
    }

    /**
     * Store a stock adjustment.
     */
    public function store(
        StoreAssetAdjustmentRequest $request,
        AssetAdjustmentService $assetAdjustmentService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $assetAdjustmentService->store($user, $request);

        return redirect()
            ->route('admin.adjustments.index')
            ->with('status', 'Stock adjustment recorded successfully.');
    }
}
