<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\ApproveAssetRequestRequest;
use App\Http\Requests\Manager\RejectAssetRequestRequest;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AssetRequests\ManagerAssetRequestReviewService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetRequestReviewController extends Controller
{
    /**
     * Display the manager's department request inbox.
     */
    public function index(Request $request, ManagerAssetRequestReviewService $managerAssetRequestReviewService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('manager.requests.index', [
            'requests' => $managerAssetRequestReviewService->paginateForManager($user),
        ]);
    }

    /**
     * Display a department request for manager review.
     */
    public function show(
        Request $request,
        AssetRequest $assetRequest,
        NotificationService $notificationService,
    ): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $notificationService->markRelatedAsRead(
            user: $user,
            resourceType: NotificationLog::RESOURCE_ASSET_REQUEST,
            resourceId: $assetRequest->id,
        );

        return view('manager.requests.show', [
            'assetRequest' => $assetRequest->load([
                'asset.category',
                'department',
                'statusHistories' => fn ($query) => $query->with('actor')->latest('created_at'),
                'user',
            ]),
        ]);
    }

    /**
     * Approve a pending request.
     */
    public function approve(
        ApproveAssetRequestRequest $request,
        AssetRequest $assetRequest,
        ManagerAssetRequestReviewService $managerAssetRequestReviewService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $managerAssetRequestReviewService->approve($assetRequest, $user, $request);

        return redirect()
            ->route('manager.requests.show', $assetRequest)
            ->with('status', 'Request approved successfully.');
    }

    /**
     * Reject a pending request.
     */
    public function reject(
        RejectAssetRequestRequest $request,
        AssetRequest $assetRequest,
        ManagerAssetRequestReviewService $managerAssetRequestReviewService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $managerAssetRequestReviewService->reject($assetRequest, $user, $request);

        return redirect()
            ->route('manager.requests.show', $assetRequest)
            ->with('status', 'Request rejected successfully.');
    }
}
