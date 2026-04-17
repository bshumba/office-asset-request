<?php

namespace App\Http\Controllers\Staff;

use App\Enums\RequestPriorityEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreAssetRequestRequest;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AssetRequests\StaffAssetRequestService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetRequestController extends Controller
{
    /**
     * Display the authenticated staff user's requests.
     */
    public function index(Request $request, StaffAssetRequestService $staffAssetRequestService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('staff.requests.index', [
            'requests' => $staffAssetRequestService->paginateForUser($user),
        ]);
    }

    /**
     * Show the request creation form.
     */
    public function create(Request $request, StaffAssetRequestService $staffAssetRequestService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('staff.requests.create', [
            'assets' => $staffAssetRequestService->availableAssetsForUser($user),
            'priorities' => RequestPriorityEnum::cases(),
        ]);
    }

    /**
     * Store a new staff asset request.
     */
    public function store(
        StoreAssetRequestRequest $request,
        StaffAssetRequestService $staffAssetRequestService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $assetRequest = $staffAssetRequestService->create($user, $request);

        return redirect()
            ->route('staff.requests.show', $assetRequest)
            ->with('status', 'Asset request submitted successfully.');
    }

    /**
     * Display a single staff request.
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

        return view('staff.requests.show', [
            'assetRequest' => $assetRequest->load([
                'asset.category',
                'department',
                'statusHistories' => fn ($query) => $query->with('actor')->latest('created_at'),
            ]),
        ]);
    }

    /**
     * Cancel a pending staff request.
     */
    public function cancel(
        Request $request,
        AssetRequest $assetRequest,
        StaffAssetRequestService $staffAssetRequestService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $staffAssetRequestService->cancel($assetRequest, $user);

        return redirect()
            ->route('staff.requests.show', $assetRequest)
            ->with('status', 'Asset request cancelled successfully.');
    }
}
