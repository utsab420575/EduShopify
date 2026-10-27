<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Backend\Admin\Concerns\LiveFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use InteractsWithAdmin, LiveFilters;

    private const STATUSES = ['pending_verification' => 'Pending Verification', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deleted' => 'Deleted'];

    private const ACCOUNT_KINDS = ['buyer' => 'Buyer account', 'supplier' => 'Supplier account', 'none' => 'No account'];

    public function index(Request $request)
    {
        $this->authorize('platform.users.view');

        $search = trim($request->string('search')->toString());
        $selected = [
            'status' => $this->selectedFilter($request, 'status', self::STATUSES),
            'account' => $this->selectedFilter($request, 'account', self::ACCOUNT_KINDS),
        ];

        $applyAccountKind = fn ($q, string $kind) => $kind === 'none'
            ? $q->whereDoesntHave('accountMember')
            : $q->whereHas('accountMember.account.capabilities.capabilityType', fn ($q2) => $q2->where('code', $kind));

        // Each option's count reflects every *other* active filter, so ticking
        // a box never changes the numbers beside its siblings.
        $query = fn (?string $except = null) => User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($except !== 'status' && $selected['status'], fn ($q) => $q->whereIn('status', $selected['status']))
            ->when($except !== 'account' && $selected['account'], function ($q) use ($selected, $applyAccountKind) {
                $q->where(fn ($q2) => collect($selected['account'])->each(fn ($kind) => $q2->orWhere(fn ($q3) => $applyAccountKind($q3, $kind))));
            });

        $counts = [
            'status' => $this->countBy($query('status'), 'status'),
            'account' => $this->countEach($query('account'), self::ACCOUNT_KINDS, $applyAccountKind),
        ];

        $users = $query()
            ->with(['accountMember.account.capabilities.capabilityType', 'roles', 'socialAccounts'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->liveFilterResponse($request, 'backend.admin.users.index', 'backend.admin.users.partials._table', [
            'users' => $users,
            // Supplier users get "Edit Supplier Profile" / "Manage Products" actions.
            'canManageSuppliers' => $request->user()->can('platform.suppliers.manage'),
            'search' => $search,
            'filters' => [
                'status' => ['label' => 'Status', 'options' => self::STATUSES, 'selected' => $selected['status']],
                'account' => ['label' => 'Account', 'options' => self::ACCOUNT_KINDS, 'selected' => $selected['account']],
            ],
        ], $counts);
    }

    public function show(User $user)
    {
        $this->authorize('platform.users.view');

        $user->load(['accountMember.account.capabilities.capabilityType', 'roles', 'socialAccounts']);

        return view('backend.admin.users.show', ['targetUser' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:pending_verification,active,inactive,suspended,deleted'],
        ]);

        $user->update($validated);

        activity('moderation')->causedBy($this->admin())->performedOn($user)
            ->withProperties($validated)
            ->log('User details updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        abort_if($user->id === $this->admin()->id, 422, 'You cannot delete your own account.');

        $user->update(['status' => 'deleted']);
        $user->delete();

        activity('moderation')->causedBy($this->admin())->performedOn($user)->log('User deleted');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return back()->with('success', 'User deleted successfully.');
    }

    public function suspend(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        abort_if($user->id === $this->admin()->id, 422, 'You cannot suspend your own account.');

        $user->update(['status' => 'suspended']);

        activity('moderation')->causedBy($this->admin())->performedOn($user)
            ->withProperties(['reason' => $request->string('reason')])
            ->log('User suspended');

        return back()->with('success', 'User suspended.');
    }

    public function reactivate(User $user)
    {
        $this->authorize('platform.users.suspend');

        $user->update(['status' => 'active']);

        activity('moderation')->causedBy($this->admin())->performedOn($user)->log('User reactivated');

        return back()->with('success', 'User reactivated.');
    }
}
