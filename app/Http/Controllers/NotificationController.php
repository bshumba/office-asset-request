<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display notifications for the signed-in user.
     */
    public function index(Request $request, NotificationService $notificationService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('notifications.index', [
            'notifications' => $notificationService->paginateForUser($user),
            'unreadCount' => $notificationService->unreadCountForUser($user),
        ]);
    }

    /**
     * Open a notification destination and mark it as read.
     */
    public function open(
        Request $request,
        int $notification,
        NotificationService $notificationService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return redirect()->to($notificationService->open($user, $notification));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(
        Request $request,
        int $notification,
        NotificationService $notificationService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $notificationService->markAsRead($user, $notification);

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllAsRead(Request $request, NotificationService $notificationService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $updatedCount = $notificationService->markAllAsRead($user);

        return back()->with(
            'status',
            $updatedCount > 0
                ? 'All notifications marked as read.'
                : 'There were no unread notifications to update.',
        );
    }
}
