<?php

namespace App\Http\Controllers\Backend\Supplier\Organization;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountOwnershipTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnershipController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        return view('backend.supplier.organization.ownership.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'owners' => $account->members()->active()->owners()->with('user')->get(),
            'members' => $account->members()->active()->where('member_type', 'member')->with('user')->get(),
            'transfers' => $account->ownershipTransfers()->with(['fromUser', 'toUser'])->latest()->get(),
        ]);
    }

    public function transfer(Request $request)
    {
        $account = $this->currentAccount();
        $user = $this->currentUser();

        abort_unless($account->isOrganization(), 403);

        $validated = $request->validate([
            'target_user_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $isPrimaryOwner = $account->members()->where('user_id', $user->id)->where('is_primary_owner', true)->exists();
        abort_unless($isPrimaryOwner, 403, 'Only the primary owner can initiate an ownership transfer.');

        $targetIsMember = $account->members()->active()->where('user_id', $validated['target_user_id'])->exists();
        abort_unless($targetIsMember, 422, 'The selected user is not an active member of this account.');

        $account->ownershipTransfers()->create([
            'from_user_id' => $user->id,
            'to_user_id' => $validated['target_user_id'],
            'requested_by_user_id' => $user->id,
            'status' => 'pending',
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Ownership transfer initiated.');
    }

    public function cancel(AccountOwnershipTransfer $transfer)
    {
        abort_unless($transfer->account_id === $this->currentAccount()->id, 403);
        abort_unless($transfer->status === 'pending', 422, 'Only a pending transfer can be cancelled.');

        $transfer->update(['status' => 'cancelled']);

        return back()->with('success', 'Ownership transfer cancelled.');
    }

    /**
     * The proposed new owner accepts — performs the actual ownership swap
     * transactionally so the account is never briefly without an owner
     * (supplier_dashboard_workflow.md Part 12.6).
     */
    public function accept(AccountOwnershipTransfer $transfer)
    {
        $user = $this->currentUser();

        abort_unless($transfer->account_id === $this->currentAccount()->id, 403);
        abort_unless($transfer->to_user_id === $user->id, 403, 'Only the proposed new owner can respond to this transfer.');
        abort_unless($transfer->status === 'pending', 422, 'This transfer is no longer pending.');

        DB::transaction(function () use ($transfer) {
            $account = $transfer->account()->lockForUpdate()->first();

            $account->members()->where('user_id', $transfer->from_user_id)->update(['is_primary_owner' => false]);
            $account->members()->where('user_id', $transfer->to_user_id)->update(['is_primary_owner' => true]);
            $account->update(['primary_owner_user_id' => $transfer->to_user_id]);

            $transfer->update([
                'status' => 'completed',
                'accepted_at' => now(),
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('supplier.ownership.index')->with('success', 'You are now the primary owner of this account.');
    }

    public function reject(AccountOwnershipTransfer $transfer)
    {
        $user = $this->currentUser();

        abort_unless($transfer->account_id === $this->currentAccount()->id, 403);
        abort_unless($transfer->to_user_id === $user->id, 403, 'Only the proposed new owner can respond to this transfer.');
        abort_unless($transfer->status === 'pending', 422, 'This transfer is no longer pending.');

        $transfer->update(['status' => 'rejected']);

        return back()->with('success', 'Ownership transfer declined.');
    }
}
