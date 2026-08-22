<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $accounts = Account::where('is_system_account', false)
            ->whereHas('capabilities', fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'buyer')))
            ->when($request->filled('search'), fn ($q) => $q->where('display_name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->whereHas('capabilities', fn ($q2) => $q2->whereHas('capabilityType', fn ($q3) => $q3->where('code', 'buyer'))->where('status', $request->string('status')));
            })
            ->with(['buyerProfile', 'capabilities' => fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'buyer'))])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.buyers.index', [
            'accounts' => $accounts,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Account $account)
    {
        $this->authorize('platform.accounts.view');

        abort_unless($account->hasCapability('buyer'), 404);

        $account->load(['buyerProfile.country', 'buyerTypes', 'locations']);
        $buyerCapability = $account->capabilities()->whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))->with('applicationHistory')->first();

        return view('backend.admin.buyers.show', [
            'account' => $account,
            'capability' => $buyerCapability,
            'rfqCount' => $account->rfqs()->count(),
            'openRfqCount' => $account->rfqs()->where('status', 'open')->count(),
            'awardCount' => $account->buyerAwards()->count(),
            'poCount' => $account->buyerPurchaseOrders()->count(),
        ]);
    }
}
