<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for one or many users.
     *
     * @param  User|EloquentCollection<int, User>|array<int, User>  $recipients
     */
    public function notify(
        User|EloquentCollection|array $recipients,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
    ): void {
        $this->normalizeRecipients($recipients)
            ->unique('id')
            ->each(function (User $user) use ($type, $title, $message, $actionUrl, $resourceType, $resourceId): void {
                NotificationLog::query()->create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                    'resource_type' => $resourceType,
                    'resource_id' => $resourceId,
                ]);
            });
    }

    /**
     * Paginate a user's notifications.
     */
    public function paginateForUser(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $user->notificationsLog()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get the unread notification count for a user.
     */
    public function unreadCountForUser(User $user): int
    {
        return $user->notificationsLog()
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark a single notification as read if it belongs to the user.
     */
    public function markAsRead(User $user, int $notificationId): NotificationLog
    {
        /** @var NotificationLog $notification */
        $notification = $user->notificationsLog()->findOrFail($notificationId);

        if ($notification->read_at === null) {
            $notification->forceFill([
                'read_at' => now(),
            ])->save();
        }

        return $notification;
    }

    /**
     * Mark a notification as read and resolve its destination URL.
     */
    public function open(User $user, int $notificationId): string
    {
        $notification = $this->markAsRead($user, $notificationId);

        return $notification->action_url ?: route('notifications.index');
    }

    /**
     * Mark every unread notification as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notificationsLog()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    /**
     * Mark notifications linked to a specific resource as read.
     */
    public function markRelatedAsRead(User $user, string $resourceType, int $resourceId): int
    {
        return $user->notificationsLog()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    /**
     * @param  User|EloquentCollection<int, User>|array<int, User>  $recipients
     * @return Collection<int, User>
     */
    private function normalizeRecipients(User|EloquentCollection|array $recipients): Collection
    {
        if ($recipients instanceof User) {
            return collect([$recipients]);
        }

        if ($recipients instanceof EloquentCollection) {
            return $recipients->values();
        }

        return collect($recipients)->filter(fn (mixed $recipient): bool => $recipient instanceof User)->values();
    }
}
