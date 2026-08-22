<?php

namespace App\Http\Controllers\Backend\Supplier\Billing;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function current()
    {
        $account = $this->currentAccount();
        $subscription = $account->activeSubscription;

        return view('backend.supplier.subscription.current', [
            'account' => $account,
            'user' => $this->currentUser(),
            'subscription' => $subscription,
            'plan' => $subscription?->plan,
        ]);
    }

    public function plans()
    {
        $account = $this->currentAccount();
        $subscription = $account->activeSubscription;
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('backend.supplier.subscription.plans', [
            'account' => $account,
            'user' => $this->currentUser(),
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }
}
