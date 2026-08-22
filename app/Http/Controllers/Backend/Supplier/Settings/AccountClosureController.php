<?php

namespace App\Http\Controllers\Backend\Supplier\Settings;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountClosureController extends Controller
{
    use InteractsWithSupplierAccount;

    public function edit()
    {
        $account = $this->currentAccount();

        return view('backend.supplier.settings.close-account', [
            'account' => $account,
            'user' => $this->currentUser(),
            'blockers' => $this->outstandingBlockers($account),
        ]);
    }

    public function request(Request $request)
    {
        $account = $this->currentAccount();

        $blockers = $this->outstandingBlockers($account);
        abort_if($blockers->isNotEmpty(), 422, 'Resolve outstanding items before closing your account: '.$blockers->implode(', '));

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $account->update([
            'status' => 'deletion_pending',
            'deletion_requested_at' => now(),
            'deletion_reason' => $validated['reason'],
        ]);

        return redirect()->route('login')->with('success', 'Your account closure request has been submitted.');
    }

    private function outstandingBlockers($account): \Illuminate\Support\Collection
    {
        $blockers = collect();

        if ($account->activeSubscription) {
            $blockers->push('an active subscription');
        }
        if ($account->listings()->where('approval_status', 'approved')->exists()) {
            $blockers->push('published listings');
        }
        if ($account->quotations()->whereIn('status', ['submitted', 'under_review', 'shortlisted'])->exists()) {
            $blockers->push('submitted quotations');
        }
        if ($account->supplierAwards()->where('status', 'pending_supplier_response')->exists()) {
            $blockers->push('pending awards');
        }
        if ($account->supplierPurchaseOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            $blockers->push('active purchase orders');
        }
        if ($account->tickets()->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            $blockers->push('open support tickets');
        }

        return $blockers;
    }
}
