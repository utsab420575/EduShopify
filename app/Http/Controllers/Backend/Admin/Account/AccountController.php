<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $accounts = Account::where('is_system_account', false)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('display_name', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%"));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('account_type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['primaryOwner', 'capabilities.capabilityType', 'locations', 'members.user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.accounts.index', [
            'accounts' => $accounts,
            'search' => $request->string('search')->toString(),
            'type' => $request->string('type')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
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
