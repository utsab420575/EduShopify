<?php

namespace App\Http\Controllers\Backend\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\RfqQuestion;
use App\Services\BuyerOnboardingStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request, BuyerOnboardingStateService $onboardingState)
    {
        $user = Auth::user();
        $account = $user->activateTeamContext();

        $hasBuyerCapability = $account?->capabilities()
            ->whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))
            ->exists();

        if (! $account || ! $hasBuyerCapability) {
            return redirect('/');
        }

        $capability = $account->buyerCapability;

        if ($capability?->status === 'draft') {
            return redirect($onboardingState->resolve($user));
        }

        $data = [
            'account' => $account,
            'capabilityStatus' => $capability?->status ?? 'draft',
            'revisionReason' => $capability?->revision_reason,
            'rejectionReason' => $capability?->rejection_reason,
            'suspensionReason' => $capability?->suspension_reason,
        ];

        if (($capability?->status ?? null) !== 'active') {
            return view('backend.buyer.dashboard.index', $data);
        }

        $rfqIds = $account->rfqs()->pluck('id');

        $data += [
            'openRfqCount' => $account->rfqs()->where('status', 'open')->count(),
            'draftRfqCount' => $account->rfqs()->where('status', 'draft')->count(),
            'quotationsAwaitingReview' => Quotation::whereIn('rfq_id', $rfqIds)
                ->whereIn('status', ['submitted', 'under_review', 'revised'])
                ->count(),
            'shortlistedCount' => $account->shortlists()->count(),
            'pendingQuestionsCount' => RfqQuestion::whereIn('rfq_id', $rfqIds)->where('status', 'pending')->count(),
            'awardsAwaitingResponse' => $account->buyerAwards()->where('status', 'pending_supplier_response')->count(),
            'acceptedAwardsCount' => $account->buyerAwards()->where('status', 'accepted')->count(),
            'activePoCount' => $account->buyerPurchaseOrders()->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'poAwaitingCompletion' => $account->buyerPurchaseOrders()->where('status', 'delivered')->count(),
            'completedPoCount' => $account->buyerPurchaseOrders()->where('status', 'completed')->count(),
            'openTicketsCount' => $account->tickets()->whereNotIn('status', ['resolved', 'closed'])->count(),

            'upcomingDeadlines' => $account->rfqs()
                ->where('status', 'open')
                ->where('quotation_deadline', '>=', now())
                ->where('quotation_deadline', '<=', now()->addDays(3))
                ->orderBy('quotation_deadline')
                ->limit(5)
                ->get(['id', 'rfq_number', 'title', 'quotation_deadline']),

            'recentRfqs' => $account->rfqs()->latest()->limit(5)->get(),
            'recentQuotations' => Quotation::whereIn('rfq_id', $rfqIds)
                ->with(['rfq', 'supplierAccount.supplierProfile'])
                ->latest('submitted_at')
                ->limit(5)
                ->get(),
            'recentAwards' => $account->buyerAwards()->with(['rfq', 'supplierAccount.supplierProfile'])->latest('awarded_at')->limit(5)->get(),
            'recentPurchaseOrders' => $account->buyerPurchaseOrders()->with(['rfq', 'supplierAccount.supplierProfile'])->latest('issued_at')->limit(5)->get(),
        ];

        return view('backend.buyer.dashboard.index', $data);
    }
}
