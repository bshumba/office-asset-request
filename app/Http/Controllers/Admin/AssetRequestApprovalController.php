<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveAssetRequestRequest;
use App\Http\Requests\Admin\RejectAssetRequestRequest;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AssetRequests\AdminAssetRequestApprovalService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetRequestApprovalController extends Controller
{
    /**
     * Display the admin request inbox.
     */
    public function index(AdminAssetRequestApprovalService $adminAssetRequestApprovalService): View
    {
        return view('admin.requests.index', [
            'requests' => $adminAssetRequestApprovalService->paginateForAdmin(),
        ]);
    }

    /**
     * Display a request for admin review.
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

        return view('admin.requests.show', [
            'assetRequest' => $assetRequest->load([
                'asset.category',
                'department',
                'issue',
                'statusHistories' => fn ($query) => $query->with('actor')->latest('created_at'),
                'user',
            ]),
        ]);
    }

    /**
     * Approve a manager-approved request.
     */
    public function approve(
        ApproveAssetRequestRequest $request,
        AssetRequest $assetRequest,
        AdminAssetRequestApprovalService $adminAssetRequestApprovalService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $adminAssetRequestApprovalService->approve($assetRequest, $user, $request);

        return redirect()
            ->route('admin.requests.show', $assetRequest)
            ->with('status', 'Request approved successfully.');
    }

    /**
     * Reject a request during admin review.
     */
    public function reject(
        RejectAssetRequestRequest $request,
        AssetRequest $assetRequest,
        AdminAssetRequestApprovalService $adminAssetRequestApprovalService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $adminAssetRequestApprovalService->reject($assetRequest, $user, $request);

        return redirect()
            ->route('admin.requests.show', $assetRequest)
            ->with('status', 'Request rejected successfully.');
    }
}
