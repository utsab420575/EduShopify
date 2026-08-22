<?php

namespace App\Http\Controllers\Backend\Buyer\Settings;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountClosureController extends Controller
{
    use InteractsWithBuyerAccount;

    public function edit()
    {
        $account = $this->currentAccount();

        return view('backend.buyer.settings.close-account', [
            'blockers' => $this->outstandingBlockers($account),
        ]);
    }

    public function request(Request $request)
    {
        $account = $this->currentAccount();

        $blockers = $this->outstandingBlockers($account);
        abort_if($blockers->isNotEmpty(), 422, 'Resolve outstanding items before closing your account: '.$blockers->implode(', '));

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $account->update([
            'status' => 'deletion_pending',
            'deletion_requested_at' => now(),
            'deletion_reason' => $request->string('reason'),
        ]);

        return redirect()->route('login')->with('success', 'Your account closure request has been submitted.');
    }

    private function outstandingBlockers($account): \Illuminate\Support\Collection
    {
        $blockers = collect();

        if ($account->rfqs()->whereIn('status', ['open', 'award_pending'])->exists()) {
            $blockers->push('open RFQs');
        }
        if ($account->buyerAwards()->where('status', 'pending_supplier_response')->exists()) {
            $blockers->push('pending awards');
        }
        if ($account->buyerPurchaseOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            $blockers->push('active purchase orders');
        }
        if ($account->tickets()->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            $blockers->push('open support tickets');
        }

        return $blockers;
    }
}
