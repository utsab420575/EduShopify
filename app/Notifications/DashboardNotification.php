<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * A small, generic in-app (database channel) notification for dashboard
 * events that don't warrant their own dedicated Notification class — shows
 * up in the Buyer/Supplier notification bell via $user->notifications().
 */
class DashboardNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $message,
        public readonly ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
