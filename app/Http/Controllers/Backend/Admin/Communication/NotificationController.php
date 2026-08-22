<?php

namespace App\Http\Controllers\Backend\Admin\Communication;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    use InteractsWithAdmin;

    public function index()
    {
        return view('backend.admin.communication.notifications.index', [
            'notifications' => $this->admin()->notifications()->paginate(20),
        ]);
    }

    public function markRead(string $notification)
    {
        $this->admin()->notifications()->where('id', $notification)->first()?->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        $this->admin()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
