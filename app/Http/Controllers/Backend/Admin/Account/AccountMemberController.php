<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Backend\Admin\Concerns\LiveFilters;
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
    use InteractsWithAdmin, LiveFilters;

    private const STATUSES = ['invited' => 'Invited', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'removed' => 'Removed'];

    private const ROLES = ['primary_owner' => 'Primary Owner', 'owner' => 'Owner', 'member' => 'Member'];

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $search = trim($request->string('search')->toString());
        $selected = [
            'status' => $this->selectedFilter($request, 'status', self::STATUSES),
            'role' => $this->selectedFilter($request, 'role', self::ROLES),
        ];

        // Mirrors the Type column: a primary owner shows as "Primary Owner"
        // whatever their member_type, everyone else by member_type.
        $hasRole = fn ($q, string $role) => $role === 'primary_owner'
            ? $q->where('is_primary_owner', true)
            : $q->where('is_primary_owner', false)->where('member_type', $role);

        $query = fn (?string $except = null) => AccountMember::query()
            ->whereHas('account', fn ($q) => $q->where('is_system_account', false))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->whereHas('user', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($q3) => $q3->where('display_name', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%")));
            })
            ->when($except !== 'status' && $selected['status'], fn ($q) => $q->whereIn('status', $selected['status']))
            ->when($except !== 'role' && $selected['role'], function ($q) use ($selected, $hasRole) {
                $q->where(fn ($q2) => collect($selected['role'])->each(fn ($role) => $q2->orWhere(fn ($q3) => $hasRole($q3, $role))));
            });

        $counts = [
            'status' => $this->countBy($query('status'), 'status'),
            'role' => $this->countEach($query('role'), self::ROLES, $hasRole),
        ];

        $members = $query()
            ->with(['user', 'account'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->liveFilterResponse($request, 'backend.admin.account-members.index', 'backend.admin.account-members.partials._table', [
            'members' => $members,
            'search' => $search,
            'filters' => [
                'status' => ['label' => 'Status', 'options' => self::STATUSES, 'selected' => $selected['status']],
                'role' => ['label' => 'Type', 'options' => self::ROLES, 'selected' => $selected['role']],
            ],
        ], $counts);
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
