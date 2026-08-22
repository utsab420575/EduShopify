<?php

namespace App\Http\Controllers\Backend\Supplier\Communication;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        $user = $this->currentUser();

        $notifications = $user->notifications()->paginate(15);

        return view('backend.supplier.notifications.index', [
            'account' => $account,
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $notificationId)
    {
        $user = $this->currentUser();
        $user->unreadNotifications()->where('id', $notificationId)->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        $user = $this->currentUser();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
