<?php

namespace App\Http\Controllers\Backend\Buyer\Organization;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Organization\StoreInvitationRequest;
use App\Models\AccountMemberInvitation;
use App\Models\User;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        abort_unless($this->currentAccount()->isOrganization(), 403);

        $invitations = $this->currentAccount()->memberInvitations()->with('invitedBy')->latest()->get();

        return view('backend.buyer.organization.invitations.index', ['invitations' => $invitations]);
    }

    public function store(StoreInvitationRequest $request)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        abort_if(
            User::where('email', $request->string('invited_email'))->whereHas('accountMember', fn ($q) => $q->where('account_id', $account->id))->exists(),
            422,
            'This person already belongs to your organization.'
        );

        $rawToken = Str::random(40);

        $account->memberInvitations()->create([
            'invited_email' => $request->string('invited_email'),
            'invited_name' => $request->input('invited_name'),
            'invited_phone' => $request->input('invited_phone'),
            'invitation_mode' => User::where('email', $request->string('invited_email'))->exists() ? 'owner_prefilled' : 'employee_self_complete',
            'token_hash' => hash('sha256', $rawToken),
            'invited_by_user_id' => $this->currentUser()->id,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        return back()
            ->with('success', 'Invitation created for '.$request->string('invited_email').'.')
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
