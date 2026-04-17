<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssetIssue;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Inventory\StaffAssignedAssetService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignedAssetController extends Controller
{
    /**
     * Display assets assigned to the authenticated staff user.
     */
    public function index(Request $request, StaffAssignedAssetService $staffAssignedAssetService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('staff.issues.index', [
            'issues' => $staffAssignedAssetService->paginateForUser($user),
        ]);
    }

    /**
     * Display a single assigned asset record.
     */
    public function show(
        Request $request,
        AssetIssue $assetIssue,
        NotificationService $notificationService,
    ): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $notificationService->markRelatedAsRead(
            user: $user,
            resourceType: NotificationLog::RESOURCE_ASSET_ISSUE,
            resourceId: $assetIssue->id,
        );

        return view('staff.issues.show', [
            'assetIssue' => $assetIssue->load([
                'asset',
                'assetRequest.statusHistories' => fn ($query) => $query->with('actor')->latest('created_at'),
                'department',
                'returns.receivedByUser',
            ]),
        ]);
    }
}
