<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::active()->get();

        $freePlans    = $plans->where('billing_type', 'free')->values();
        $monthlyPlans = $plans->where('billing_type', 'monthly')->values();
        $yearlyPlans  = $plans->where('billing_type', 'yearly')->values();

        $account        = $request->attributes->get('account') ?? $request->user()?->account;
        $activeSubPlan  = null;
        $isEligibleFree = false;

        if ($account && $account->hasCapability('supplier')) {
            $activeSubPlan  = $account->activeSubscription?->plan;
            $isEligibleFree = $account->isEligibleForFreePlan();
        }

        return view('supplier.pricing', compact(
            'plans',
            'freePlans',
            'monthlyPlans',
            'yearlyPlans',
            'activeSubPlan',
            'isEligibleFree',
        ));
    }
}
