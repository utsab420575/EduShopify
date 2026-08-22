<?php

namespace App\Http\Controllers\Backend\Buyer\AccessControl;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $roles = Role::usableBy($account->id)
            ->active()
            ->whereIn('capability_scope', ['buyer', 'common', 'both'])
            ->orderBy('name')
            ->get()
            ->each(fn (Role $role) => $role->setAttribute(
                'users_count',
                $role->users()->wherePivot('account_id', $account->id)->count()
            ));

        return view('backend.buyer.access-control.roles.index', ['roles' => $roles]);
    }

    public function show(Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);

        $role->load('permissions');

        $members = $account->members()->active()->with('user')->get();
        $assignedUserIds = $role->users()->wherePivot('account_id', $account->id)->pluck('users.id');

        return view('backend.buyer.access-control.roles.show', [
            'role' => $role,
            'members' => $members,
            'assignedUserIds' => $assignedUserIds,
        ]);
    }

    public function assign(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);
        abort_if($role->is_owner_role, 422, 'Ownership roles are managed from the Ownership page.');

        $request->validate(['member_id' => ['required', 'integer', 'exists:account_members,id']]);

        $member = AccountMember::where('account_id', $account->id)->findOrFail($request->integer('member_id'));

        $member->user->assignRole($role);

        return back()->with('success', 'Role assigned.');
    }

    public function unassign(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);

        $request->validate(['member_id' => ['required', 'integer', 'exists:account_members,id']]);

        $member = AccountMember::where('account_id', $account->id)->findOrFail($request->integer('member_id'));

        $member->user->removeRole($role);

        return back()->with('success', 'Role removed.');
    }
}
