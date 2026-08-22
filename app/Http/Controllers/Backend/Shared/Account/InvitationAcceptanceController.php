<?php

namespace App\Http\Controllers\Backend\Shared\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Shared\Account\AcceptInvitationRequest;
use App\Models\AccountMember;
use App\Models\AccountMemberInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Public acceptance flow for an organization-member invitation
 * (spec 12.2 — Employee Opens Invitation → Verify Eligibility → Accept →
 * account_members → Role Assignment [separate, done later by the owner]).
 *
 * Deliberately separate from the account-registration wizard: an invited
 * member joins an EXISTING account rather than creating a new one.
 */
class InvitationAcceptanceController extends Controller
{
    public function show(string $token)
    {
        $invitation = $this->findInvitation($token);

        if (! $invitation) {
            return view('backend.shared.invitations.invalid');
        }

        return view('backend.shared.invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
            'userExists' => User::where('email', $invitation->invited_email)->exists(),
        ]);
    }

    public function accept(AcceptInvitationRequest $request, string $token)
    {
        $invitation = $this->findInvitation($token);

        abort_unless($invitation, 404);

        $existingUser = User::where('email', $invitation->invited_email)->first();

        if ($existingUser) {
            if (! Auth::check() || Auth::id() !== $existingUser->id) {
                return redirect()->route('login')
                    ->with('error', "Please sign in as {$invitation->invited_email} to accept this invitation.");
            }

            $user = $existingUser;
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            $user = User::create([
                'name' => $request->string('name'),
                'email' => $invitation->invited_email,
                'phone' => $invitation->invited_phone,
                'password' => Hash::make($request->string('password')),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            Auth::login($user);
        }

        if ($user->accountMember) {
            return back()->with('error', 'This user already belongs to an account and cannot join another.');
        }

        DB::transaction(function () use ($invitation, $user) {
            AccountMember::create([
                'account_id' => $invitation->account_id,
                'user_id' => $user->id,
                'member_type' => 'member',
                'is_primary_owner' => false,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
        });

        return redirect()->route('buyer.dashboard')->with('success', 'Welcome! You have joined the organization. An owner will assign your role shortly.');
    }

    private function findInvitation(string $token): ?AccountMemberInvitation
    {
        $invitation = AccountMemberInvitation::where('token_hash', hash('sha256', $token))
            ->where('status', 'pending')
            ->first();

        if (! $invitation) {
            return null;
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);

            return null;
        }

        return $invitation;
    }
}
