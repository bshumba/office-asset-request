<?php

namespace App\Services\Auth;

use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DashboardRedirectService
{
    /**
     * Resolve the dashboard route name for the given user.
     */
    public function resolveRouteName(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($user->isManager()) {
            return 'manager.dashboard';
        }

        if ($user->isStaff()) {
            return 'staff.dashboard';
        }

        throw new AccessDeniedHttpException('No dashboard route is available for this user.');
    }
}
