<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;
use Illuminate\Http\Request;

/**
 * Platform-wide read oversight of account membership (spec 3.3). Normal team
 * management belongs to Buyer/Supplier organization owners — Admin does not
 * routinely manage an account's employees on their behalf, so this
 * controller is intentionally read-only.
 */
class AccountMemberController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $members = AccountMember::query()
            ->whereHas('account', fn ($q) => $q->where('is_system_account', false))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($q2) => $q2->where('display_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['user', 'account'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.account-members.index', [
            'members' => $members,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function update(Request $request, AccountMember $member)
    {
        $this->authorize('platform.accounts.suspend');

        $validated = $request->validate([
            'member_type' => ['required', 'in:owner,member'],
            'status'      => ['required', 'in:invited,active,inactive,suspended,removed'],
        ]);

        if ($member->is_primary_owner && $validated['member_type'] !== 'owner') {
            return back()->with('error', 'Cannot change membership type of the primary owner.');
        }

        $member->update($validated);

        activity('moderation')->causedBy($this->admin())->performedOn($member)
            ->withProperties($validated)
            ->log('Account member status updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Account member updated successfully.']);
        }

        return back()->with('success', 'Account member updated successfully.');
    }

    public function destroy(Request $request, AccountMember $member)
    {
        $this->authorize('platform.accounts.suspend');

        if ($member->is_primary_owner) {
            return back()->with('error', 'Cannot remove primary owner. Please transfer ownership first.');
        }

        $member->update([
            'status'             => 'removed',
            'removed_at'         => now(),
            'removed_by_user_id' => $this->admin()->id,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($member)->log('Account member removed');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Account member removed successfully.']);
        }

        return back()->with('success', 'Account member removed successfully.');
    }
}
