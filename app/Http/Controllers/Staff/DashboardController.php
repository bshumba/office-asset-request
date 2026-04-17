<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardFilterRequest;
use App\Models\User;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the staff dashboard.
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

        return view('staff.dashboard', $dashboardMetricsService->forStaff($user, $request->filters()));
    }
}
