<?php

namespace Tests\Feature;

use App\Models\AccountMemberInvitation;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerInvitationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveOrganizationOwner(string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization',
            'organization_display_name' => 'Test Organization',
            'capability'   => 'buyer',
            'name'         => 'Owner User',
            'email'        => $email,
            'phone'        => '+15550009999',
            'password'     => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->buyerCapability()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    public function test_owner_can_invite_a_new_member_and_the_member_can_accept_and_join(): void
    {
        $owner = $this->makeActiveOrganizationOwner('owner@example.com');

        $this->actingAs($owner)->post(route('buyer.invitations.store'), [
            'invited_email' => 'newmember@example.com',
        ])->assertRedirect();

        $invitation = AccountMemberInvitation::where('invited_email', 'newmember@example.com')->firstOrFail();
        $this->assertSame('pending', $invitation->status);
        $this->assertSame('employee_self_complete', $invitation->invitation_mode);

        // The raw token is only ever available via the one-time flash link,
        // so recover it the same way store() generated it isn't retrievable —
        // regenerate deterministically is not possible; instead resend to get a fresh raw token.
        $response = $this->actingAs($owner)->post(route('buyer.invitations.resend', $invitation))
            ->assertRedirect();

        $inviteLink = session('inviteLink');
        $this->assertNotNull($inviteLink);
        $token = basename(parse_url($inviteLink, PHP_URL_PATH));

        $this->get(route('invitations.accept.show', $token))
            ->assertOk()
            ->assertSee('newmember@example.com');

        $this->post(route('invitations.accept.submit', $token), [
            'requires_registration' => '1',
            'name' => 'New Member',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('buyer.dashboard'));

        $newUser = User::where('email', 'newmember@example.com')->firstOrFail();

        $this->assertDatabaseHas('account_members', [
            'account_id' => $owner->account->id,
            'user_id' => $newUser->id,
            'status' => 'active',
            'member_type' => 'member',
        ]);

        $this->assertSame('accepted', $invitation->fresh()->status);
    }

    public function test_expired_invitation_link_is_rejected(): void
    {
        $owner = $this->makeActiveOrganizationOwner('owner2@example.com');

        $rawToken = 'a-known-raw-token-for-testing-purposes-1234';

        $invitation = $owner->account->memberInvitations()->create([
            'invited_email' => 'late@example.com',
            'invitation_mode' => 'employee_self_complete',
            'token_hash' => hash('sha256', $rawToken),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->subDay(),
            'status' => 'pending',
        ]);

        $this->get(route('invitations.accept.show', $rawToken))
            ->assertOk()
            ->assertSee('no longer valid');

        $this->assertSame('expired', $invitation->fresh()->status);
    }
}
