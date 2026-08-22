<?php

namespace App\Http\Controllers\Backend\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RfqSupplierQueue;
use App\Services\SupplierOnboardingStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Supplier Dashboard — main overview page.
 *
 * Mirrors the Buyer DashboardController pattern exactly.
 * Route: GET /supplier/ → supplier.dashboard
 *
 * This route deliberately sits OUTSIDE RequireSupplierCapability so that
 * a pending / rejected / revision_required Supplier still lands on a
 * status page instead of a bare 403.
 */
class DashboardController extends Controller
{
    public function index(Request $request, SupplierOnboardingStateService $onboardingState)
    {
        $user    = Auth::user();
        $account = $user->activateTeamContext();

        $hasSupplierCapability = $account?->capabilities()
            ->whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))
            ->exists();

        if (! $account || ! $hasSupplierCapability) {
            return redirect('/');
        }

        $capability = $account->supplierCapability;

        // Still in the registration wizard — should not reach here, but guard anyway.
        if ($capability?->status === 'draft') {
            return redirect($onboardingState->resolveRoute($user));
        }

        $data = [
            'account'          => $account,
            'user'             => $user,
            'capabilityStatus' => $capability?->status ?? 'draft',
            'revisionReason'   => $capability?->revision_reason,
            'rejectionReason'  => $capability?->rejection_reason,
            'suspensionReason' => $capability?->suspension_reason,
        ];

        // Not yet active — show a minimal status page (pending / rejected / revision / suspended).
        if (($capability?->status ?? null) !== 'active') {
            return view('backend.supplier.dashboard.index', $data);
        }

        // ── Active supplier — compile stats ──────────────────────────────────

        // Subscription info
        $subscription = $account->activeSubscription;

        // Catalog
        $totalListings     = $account->listings()->count();
        $draftListings     = $account->listings()->where('approval_status', 'draft')->count();
        $pendingListings   = $account->listings()->where('approval_status', 'pending')->count();
        $publishedListings = $account->listings()->where('approval_status', 'approved')->count();
        $rejectedListings  = $account->listings()->where('approval_status', 'rejected')->count();

        // RFQ Opportunities — supplier queue entries visible to this supplier
        $availableOpportunities = RfqSupplierQueue::where('supplier_account_id', $account->id)
            ->released()
            ->count();

        // Quotations
        $draftQuotations     = $account->quotations()->where('status', 'draft')->count();
        $submittedQuotations = $account->quotations()->whereIn('status', ['submitted', 'under_review'])->count();
        $revisionRequests    = $account->quotations()
            ->whereHas('revisionRequests', fn ($q) => $q->where('status', 'pending'))
            ->count();

        // Awards
        $pendingAwards   = $account->supplierAwards()->where('status', 'pending_supplier_response')->count();
        $acceptedAwards  = $account->supplierAwards()->where('status', 'accepted')->count();

        // Purchase Orders (Phase 1 simplified: issued → completed)
        $issuedPoCount    = $account->supplierPurchaseOrders()->where('status', 'issued')->count();
        $completedPoCount = $account->supplierPurchaseOrders()->where('status', 'completed')->count();

        // Communication
        $openTicketsCount = $account->tickets()->whereNotIn('status', ['resolved', 'closed'])->count();

        // Reviews
        $reviewCount = $account->receivedReviews()->where('status', 'approved')->count();

        // Upcoming deadlines
        $quotationDeadlines = RfqSupplierQueue::where('supplier_account_id', $account->id)
            ->released()
            ->with(['rfq' => fn ($q) => $q
                ->where('status', 'open')
                ->where('quotation_deadline', '>=', now())
                ->where('quotation_deadline', '<=', now()->addDays(7))
                ->orderBy('quotation_deadline')
            ])
            ->get()
            ->filter(fn ($entry) => $entry->rfq !== null)
            ->take(5);

        // Award response deadlines
        $awardDeadlines = $account->supplierAwards()
            ->where('status', 'pending_supplier_response')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '>=', now())
            ->orderBy('response_deadline')
            ->limit(5)
            ->get(['id', 'award_number', 'response_deadline']);

        // Recent activity
        $recentQuotations = $account->quotations()
            ->with(['rfq'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $recentAwards = $account->supplierAwards()
            ->with(['rfq', 'buyerAccount.buyerProfile'])
            ->latest('awarded_at')
            ->limit(5)
            ->get();

        $recentPurchaseOrders = $account->supplierPurchaseOrders()
            ->with(['rfq', 'buyerAccount.buyerProfile'])
            ->latest('issued_at')
            ->limit(5)
            ->get();

        $data += [
            'subscription'       => $subscription,
            'totalListings'      => $totalListings,
            'draftListings'      => $draftListings,
            'pendingListings'    => $pendingListings,
            'publishedListings'  => $publishedListings,
            'rejectedListings'   => $rejectedListings,
            'availableOpportunities' => $availableOpportunities,
            'draftQuotations'    => $draftQuotations,
            'submittedQuotations' => $submittedQuotations,
            'revisionRequests'   => $revisionRequests,
            'pendingAwards'      => $pendingAwards,
            'acceptedAwards'     => $acceptedAwards,
            'issuedPoCount'      => $issuedPoCount,
            'completedPoCount'   => $completedPoCount,
            'openTicketsCount'   => $openTicketsCount,
            'reviewCount'        => $reviewCount,
            'quotationDeadlines' => $quotationDeadlines,
            'awardDeadlines'     => $awardDeadlines,
            'recentQuotations'   => $recentQuotations,
            'recentAwards'       => $recentAwards,
            'recentPurchaseOrders' => $recentPurchaseOrders,
        ];

        return view('backend.supplier.dashboard.index', $data);
    }
}
