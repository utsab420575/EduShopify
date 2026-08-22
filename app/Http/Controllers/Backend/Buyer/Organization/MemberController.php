<?php

namespace App\Http\Controllers\Backend\Buyer\Organization;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;

class MemberController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $this->authorizeOrganization();

        $members = $this->currentAccount()->members()
            ->with(['user.roles'])
            ->orderByDesc('is_primary_owner')
            ->orderBy('created_at')
            ->get();

        return view('backend.buyer.organization.members.index', ['members' => $members]);
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

    private function authorizeOrganization(): void
    {
        abort_unless($this->currentAccount()->isOrganization(), 403);
    }

    private function authorizeMember(AccountMember $member): void
    {
        $this->authorizeOrganization();
        abort_unless($member->account_id === $this->currentAccount()->id, 403);
    }

    private function guardLastOwner(AccountMember $member, string $action): void
    {
        if (! $member->isOwner()) {
            return;
        }

        $activeOwners = $this->currentAccount()->members()->active()->owners()->count();

        abort_if($activeOwners <= 1, 422, "Cannot {$action} the account's last active owner.");
    }
}
