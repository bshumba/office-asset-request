<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardFilterRequest;
use App\Models\User;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
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

        return view('admin.dashboard', $dashboardMetricsService->forAdmin($request->filters()));
    }
}
