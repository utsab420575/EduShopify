<?php

namespace App\Http\Controllers\Backend\Buyer\Communication;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $notifications = $this->currentUser()->notifications()->paginate(15);

        return view('backend.buyer.notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $this->currentUser()->id, 403);

        $notification->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        $this->currentUser()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
