<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetIssueRequest;
use App\Models\AssetIssue;
use App\Models\NotificationLog;
use App\Models\AssetRequest;
use App\Models\User;
use App\Services\Inventory\AssetIssueService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetIssueController extends Controller
{
    /**
     * Display issued asset records.
     */
    public function index(AssetIssueService $assetIssueService): View
    {
        return view('admin.issues.index', [
            'issues' => $assetIssueService->paginate(),
        ]);
    }

    /**
     * Display an issue record.
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

        return view('admin.issues.show', [
            'assetIssue' => $assetIssue->load([
                'asset',
                'assetRequest.statusHistories' => fn ($query) => $query->with('actor')->latest('created_at'),
                'department',
                'issuedToUser',
                'returns.receivedByUser',
            ]),
        ]);
    }

    /**
     * Create a new asset issue from an admin-approved request.
     */
    public function store(
        StoreAssetIssueRequest $request,
        AssetRequest $assetRequest,
        AssetIssueService $assetIssueService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $assetIssue = $assetIssueService->issue($assetRequest, $user, $request);

        return redirect()
            ->route('admin.issues.show', $assetIssue)
            ->with('status', 'Asset issued successfully.');
    }
}
