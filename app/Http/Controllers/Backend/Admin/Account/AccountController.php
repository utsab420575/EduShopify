<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Backend\Admin\Concerns\LiveFilters;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    use InteractsWithAdmin, LiveFilters;

    private const TYPES = ['individual' => 'Individual', 'organization' => 'Organization'];

    private const STATUSES = ['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deletion_pending' => 'Deletion Pending', 'deleted' => 'Deleted'];

    private const CAPABILITIES = ['buyer' => 'Buyer', 'supplier' => 'Supplier'];

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $search = trim($request->string('search')->toString());
        $selected = [
            'type' => $this->selectedFilter($request, 'type', self::TYPES),
            'status' => $this->selectedFilter($request, 'status', self::STATUSES),
            'capability' => $this->selectedFilter($request, 'capability', self::CAPABILITIES),
        ];

        $hasCapability = fn ($q, $codes) => $q->whereHas('capabilities.capabilityType', fn ($q2) => $q2->whereIn('code', (array) $codes));

        $query = fn (?string $except = null) => Account::where('is_system_account', false)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('display_name', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhereHas('primaryOwner', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")));
            })
            ->when($except !== 'type' && $selected['type'], fn ($q) => $q->whereIn('account_type', $selected['type']))
            ->when($except !== 'status' && $selected['status'], fn ($q) => $q->whereIn('status', $selected['status']))
            ->when($except !== 'capability' && $selected['capability'], fn ($q) => $hasCapability($q, $selected['capability']));

        $counts = [
            'type' => $this->countBy($query('type'), 'account_type'),
            'status' => $this->countBy($query('status'), 'status'),
            'capability' => $this->countEach($query('capability'), self::CAPABILITIES, $hasCapability),
        ];

        $accounts = $query()
            ->with(['primaryOwner', 'capabilities.capabilityType', 'locations', 'members.user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->liveFilterResponse($request, 'backend.admin.accounts.index', 'backend.admin.accounts.partials._table', [
            'accounts' => $accounts,
            'search' => $search,
            'filters' => [
                'type' => ['label' => 'Type', 'options' => self::TYPES, 'selected' => $selected['type']],
                'status' => ['label' => 'Status', 'options' => self::STATUSES, 'selected' => $selected['status']],
                'capability' => ['label' => 'Capability', 'options' => self::CAPABILITIES, 'selected' => $selected['capability']],
            ],
        ], $counts);
    }

    public function show(Account $account)
    {
        $this->authorize('platform.accounts.view');

        $account->load([
            'primaryOwner', 'members.user', 'capabilities.capabilityType',
            'buyerProfile', 'supplierProfile.country', 'locations',
        ]);

        return view('backend.admin.accounts.show', ['account' => $account]);
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:200'],
            'account_type' => ['required', 'in:individual,organization'],
            'status'       => ['required', 'in:draft,pending_approval,active,inactive,suspended,deletion_pending,deleted'],
        ]);

        $account->update($validated);

        activity('moderation')->causedBy($this->admin())->performedOn($account)
            ->withProperties($validated)
            ->log('Account details updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Account updated successfully.']);
        }

        return back()->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        $account->update(['status' => 'deleted', 'deleted_at' => now(), 'deleted_by_user_id' => $this->admin()->id]);

        activity('moderation')->causedBy($this->admin())->performedOn($account)->log('Account deleted');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Account deleted successfully.']);
        }

        return back()->with('success', 'Account deleted successfully.');
    }

    public function approve(Account $account)
    {
        $this->authorize('platform.accounts.approve');

        try {
            app(AccountModerationService::class)->approve($account, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($account)->log('Account approved');

        return back()->with('success', 'Account approved.');
    }

    public function suspend(Request $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            app(AccountModerationService::class)->suspend($account, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Account suspended.');
    }

    public function reactivate(Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        try {
            app(AccountModerationService::class)->reactivate($account);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($account)->log('Account reactivated');

        return back()->with('success', 'Account reactivated.');
    }
}
