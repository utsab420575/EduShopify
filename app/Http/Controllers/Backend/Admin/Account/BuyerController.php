<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Backend\Admin\Concerns\LiveFilters;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    use InteractsWithAdmin, LiveFilters;

    private const STATUSES = ['draft' => 'Draft', 'pending' => 'Pending', 'active' => 'Active', 'revision_required' => 'Revision Required', 'rejected' => 'Rejected', 'suspended' => 'Suspended'];

    private const TYPES = ['individual' => 'Individual', 'organization' => 'Organization'];

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $search = trim($request->string('search')->toString());
        $selected = [
            'status' => $this->selectedFilter($request, 'status', self::STATUSES),
            'type' => $this->selectedFilter($request, 'type', self::TYPES),
        ];

        $buyerCapability = fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'buyer'));
        $hasStatus = fn ($q, $statuses) => $q->whereHas('capabilities', fn ($q2) => $buyerCapability($q2)->whereIn('status', (array) $statuses));

        $query = fn (?string $except = null) => Account::where('is_system_account', false)
            ->whereHas('capabilities', $buyerCapability)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('display_name', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhereHas('buyerProfile', fn ($q3) => $q3->where('organization_name', 'like', "%{$search}%")->orWhere('contact_person', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")));
            })
            ->when($except !== 'status' && $selected['status'], fn ($q) => $hasStatus($q, $selected['status']))
            ->when($except !== 'type' && $selected['type'], fn ($q) => $q->whereIn('account_type', $selected['type']));

        $counts = [
            'status' => $this->countEach($query('status'), self::STATUSES, $hasStatus),
            'type' => $this->countBy($query('type'), 'account_type'),
        ];

        $accounts = $query()
            ->with(['buyerProfile.country', 'buyerTypes', 'locations', 'primaryOwner', 'capabilities' => $buyerCapability])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->liveFilterResponse($request, 'backend.admin.buyers.index', 'backend.admin.buyers.partials._table', [
            'accounts' => $accounts,
            'search' => $search,
            'filters' => [
                'status' => ['label' => 'Capability Status', 'options' => self::STATUSES, 'selected' => $selected['status']],
                'type' => ['label' => 'Type', 'options' => self::TYPES, 'selected' => $selected['type']],
            ],
        ], $counts);
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
