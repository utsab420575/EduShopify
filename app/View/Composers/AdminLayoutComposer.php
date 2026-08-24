<?php

namespace App\View\Composers;

use App\Support\Approvals\ApprovalQueueRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Supplies the Admin backend layout with the acting user and lightweight
 * sidebar/topbar counters (Approval Center submenu badges, notification
 * bell), so controllers don't need to pass this boilerplate on every
 * response.
 */
class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return;
        }

        $view->with('user', $user);
        $view->with('unreadNotifications', $user->unreadNotifications()->count());
        $view->with('topbarNotifications', $user->unreadNotifications()->latest()->limit(5)->get());

        $approvalQueues = $this->approvalQueues($user);

        $view->with('approvalQueues', $approvalQueues);
        $view->with('approvalQueueTotal', array_sum(array_column($approvalQueues, 'count')));
    }

    private function approvalQueues($user): array
    {
        $queues = [];

        foreach (ApprovalQueueRegistry::forUser($user) as $key => $queue) {
            $queues[] = [
                'key'   => $key,
                'label' => $queue['label'],
                'icon'  => $queue['icon'],
                'count' => ($queue['count'])(),
            ];
        }

        return $queues;
    }
}
