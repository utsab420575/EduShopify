<?php

namespace App\View\Composers;

use App\Models\AccountCapability;
use App\Models\AccountConversionRequest;
use App\Models\AttributeSuggestion;
use App\Models\Brand;
use App\Models\CategorySuggestion;
use App\Models\Listing;
use App\Models\ReviewReport;
use App\Models\RoleRequest;
use App\Models\SupplierDocument;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Supplies the Admin backend layout with the acting user and lightweight
 * sidebar/topbar counters (Approval Center badge, notification bell), so
 * controllers don't need to pass this boilerplate on every response.
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
        $view->with('approvalQueueTotal', $this->approvalQueueTotal());
    }

    private function approvalQueueTotal(): int
    {
        return AccountCapability::where('status', 'pending')->count()
            + SupplierDocument::where('status', 'pending')->count()
            + Listing::where('approval_status', 'pending')->count()
            + CategorySuggestion::where('status', 'pending')->count()
            + AttributeSuggestion::where('status', 'pending')->count()
            + Brand::where('approval_status', 'pending')->count()
            + Unit::where('approval_status', 'pending')->count()
            + RoleRequest::where('status', 'pending')->count()
            + AccountConversionRequest::where('status', 'pending')->count()
            + ReviewReport::where('status', 'pending')->count();
    }
}
