<?php

namespace App\Http\Controllers\Backend\Supplier\Organization;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountMemberInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $invitations = $account->memberInvitations()->with('invitedBy')->latest()->get();

        return view('backend.supplier.organization.invitations.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'invitations' => $invitations,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $validated = $request->validate([
            'invited_email' => ['required', 'email'],
            'invited_name' => ['nullable', 'string', 'max:255'],
            'invited_phone' => ['nullable', 'string', 'max:50'],
        ]);

        abort_if(
            User::where('email', $validated['invited_email'])->whereHas('accountMember', fn ($q) => $q->where('account_id', $account->id))->exists(),
            422,
            'This person already belongs to your organization.'
        );

        $rawToken = Str::random(40);

        $account->memberInvitations()->create([
            'invited_email' => $validated['invited_email'],
            'invited_name' => $validated['invited_name'] ?? null,
            'invited_phone' => $validated['invited_phone'] ?? null,
            'invitation_mode' => User::where('email', $validated['invited_email'])->exists() ? 'owner_prefilled' : 'employee_self_complete',
            'token_hash' => hash('sha256', $rawToken),
            'invited_by_user_id' => $this->currentUser()->id,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        return back()
            ->with('success', 'Invitation created for ' . $validated['invited_email'] . '.')
            ->with('inviteLink', route('invitations.accept.show', $rawToken));
    }

    public function resend(AccountMemberInvitation $invitation)
    {
        $this->authorizeInvitation($invitation);

        $rawToken = Str::random(40);

        $invitation->update([
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        return back()
            ->with('success', 'Invitation link regenerated.')
            ->with('inviteLink', route('invitations.accept.show', $rawToken));
    }

    public function cancel(AccountMemberInvitation $invitation)
    {
        $this->authorizeInvitation($invitation);

        $invitation->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'Invitation cancelled.');
    }

    private function authorizeInvitation(AccountMemberInvitation $invitation): void
    {
        abort_unless($invitation->account_id === $this->currentAccount()->id, 403);
    }
}
