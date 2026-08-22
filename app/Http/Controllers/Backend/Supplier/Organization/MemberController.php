<?php

namespace App\Http\Controllers\Backend\Supplier\Organization;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $members = $account->members()
            ->with(['user.roles'])
            ->orderByDesc('is_primary_owner')
            ->orderBy('created_at')
            ->get();

        return view('backend.supplier.organization.members.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'members' => $members,
        ]);
    }

    public function suspend(AccountMember $member)
    {
        $this->authorizeMember($member);
        $this->guardLastOwner($member, 'suspend');

        $member->update(['status' => 'suspended']);

        return back()->with('success', 'Member suspended.');
    }

    public function activate(AccountMember $member)
    {
        $this->authorizeMember($member);

        $member->update(['status' => 'active']);

        return back()->with('success', 'Member activated.');
    }

    public function destroy(AccountMember $member)
    {
        $this->authorizeMember($member);
        $this->guardLastOwner($member, 'remove');

        $member->update([
            'status' => 'removed',
            'removed_at' => now(),
            'removed_by_user_id' => $this->currentUser()->id,
        ]);

        return back()->with('success', 'Member removed.');
    }

    private function authorizeMember(AccountMember $member): void
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($member->account_id === $account->id, 403);
    }

    private function guardLastOwner(AccountMember $member, string $action): void
    {
        if (! $member->isOwner()) {
            return;
        }

        $activeOwners = $this->currentAccount()->members()->where('status', 'active')->where('is_primary_owner', true)->count();
        abort_if($activeOwners <= 1, 422, "Cannot {$action} the account's last active owner.");
    }
}
