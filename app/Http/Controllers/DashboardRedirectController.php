<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Redirect authenticated users to their role dashboard.
     */
    public function __invoke(Request $request, DashboardRedirectService $dashboardRedirectService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return redirect()->route($dashboardRedirectService->resolveRouteName($user));
    }
}
