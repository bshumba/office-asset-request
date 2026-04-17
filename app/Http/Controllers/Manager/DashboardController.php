<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardFilterRequest;
use App\Models\User;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the manager dashboard.
     */
    public function __invoke(
        DashboardFilterRequest $request,
        DashboardMetricsService $dashboardMetricsService,
    ): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('manager.dashboard', $dashboardMetricsService->forManager($user, $request->filters()));
    }
}
