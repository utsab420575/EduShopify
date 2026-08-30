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
            ->with(['buyerProfile.country', 'buyerTypes', 'locations', 'primaryOwner', 'capabilities' => fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'buyer'))])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.buyers.index', [
            'accounts' => $accounts,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');
        abort_unless($account->hasCapability('buyer'), 404);

        $validated = $request->validate([
            'display_name'      => ['required', 'string', 'max:200'],
            'organization_name' => ['nullable', 'string', 'max:200'],
            'contact_person'    => ['nullable', 'string', 'max:150'],
            'email'             => ['nullable', 'email', 'max:150'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'address'           => ['nullable', 'string'],
        ]);

        $account->update(['display_name' => $validated['display_name']]);

        if ($account->buyerProfile) {
            $account->buyerProfile->update($validated);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($account)
            ->withProperties($validated)
            ->log('Buyer profile updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Buyer profile updated successfully.']);
        }

        return back()->with('success', 'Buyer profile updated successfully.');
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
