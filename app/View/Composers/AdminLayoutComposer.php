<?php

namespace App\View\Composers;

use App\Models\AccountAchievement;
use App\Models\BlogPost;
use App\Models\Certification;
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

        if ($user->can('platform.achievements.review') || $user->can('platform.certifications.review')) {
            $achievementRequestsPending = $user->can('platform.achievements.review')
                ? AccountAchievement::where('status', 'pending')->count()
                : 0;
            $certificationRequestsPending = $user->can('platform.certifications.review')
                ? Certification::where('status', 'pending')->count()
                : 0;

            $view->with('achievementRequestsPending', $achievementRequestsPending);
            $view->with('certificationRequestsPending', $certificationRequestsPending);
            $view->with('achievementsPendingCount', $achievementRequestsPending + $certificationRequestsPending);
        }

        if ($user->can('platform.blog.review')) {
            $view->with('blogPendingCount', BlogPost::where('status', 'pending')->count());
        }
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
