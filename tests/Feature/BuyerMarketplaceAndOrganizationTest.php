<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerMarketplaceAndOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveAccount(string $accountType, string $capability, string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => $accountType,
            'organization_display_name' => $accountType === 'organization' ? 'Test Organization' : null,
            'capability'   => $capability,
            'name'         => ucfirst($capability).' Test User',
            'email'        => $email,
            'phone'        => '+15550009999',
            'password'     => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->{$capability.'Capability'}()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    public function test_marketplace_products_and_services_pages_render_for_an_active_buyer(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer1@example.com');

        $this->actingAs($buyer)->get(route('buyer.marketplace.products.index'))
            ->assertOk()
            ->assertSee('Products');

        $this->actingAs($buyer)->get(route('buyer.marketplace.services.index'))
            ->assertOk()
            ->assertSee('Services');
    }

    public function test_saved_items_page_renders_each_type_tab(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer2@example.com');

        foreach (['supplier', 'listing', 'rfq', 'quotation'] as $type) {
            $this->actingAs($buyer)->get(route('buyer.saved-items.index', ['type' => $type]))
                ->assertOk();
        }
    }

    public function test_reviews_notifications_and_locations_pages_render(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer3@example.com');

        $this->actingAs($buyer)->get(route('buyer.reviews.index'))->assertOk();
        $this->actingAs($buyer)->get(route('buyer.notifications.index'))->assertOk();
        $this->actingAs($buyer)->get(route('buyer.locations.index'))->assertOk();
    }

    public function test_settings_pages_render_and_security_update_works(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer4@example.com');

        $this->actingAs($buyer)->get(route('buyer.settings.security'))->assertOk();
        $this->actingAs($buyer)->get(route('buyer.settings.conversion'))->assertOk();
        $this->actingAs($buyer)->get(route('buyer.settings.close-account'))->assertOk();

        $this->actingAs($buyer)->put(route('buyer.settings.security.update'), [
            'name' => 'Renamed Buyer',
            'email' => $buyer->email,
        ])->assertRedirect();

        $this->assertSame('Renamed Buyer', $buyer->fresh()->name);
    }

    public function test_dashboard_mode_page_requires_both_capabilities(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer5@example.com');

        $this->actingAs($buyer)->get(route('buyer.settings.dashboard-mode'))->assertNotFound();
    }

    public function test_organization_pages_are_reachable_for_an_organization_account(): void
    {
        $owner = $this->makeActiveAccount('organization', 'buyer', 'owner1@example.com');

        $this->actingAs($owner)->get(route('buyer.members.index'))
            ->assertOk()
            ->assertSee('Owner User' === $owner->name ? $owner->name : $owner->name);

        $this->actingAs($owner)->get(route('buyer.invitations.index'))->assertOk();
        $this->actingAs($owner)->get(route('buyer.roles.index'))->assertOk();
        $this->actingAs($owner)->get(route('buyer.permissions.index'))->assertOk();
        $this->actingAs($owner)->get(route('buyer.role-requests.index'))->assertOk();
        $this->actingAs($owner)->get(route('buyer.ownership.index'))->assertOk();
    }

    public function test_organization_only_pages_are_blocked_for_an_individual_account(): void
    {
        $buyer = $this->makeActiveAccount('individual', 'buyer', 'buyer6@example.com');

        $this->actingAs($buyer)->get(route('buyer.members.index'))->assertForbidden();
        $this->actingAs($buyer)->get(route('buyer.roles.index'))->assertForbidden();
    }

    public function test_owner_can_submit_a_role_request_for_admin_review(): void
    {
        $owner = $this->makeActiveAccount('organization', 'buyer', 'owner2@example.com');

        $permission = \App\Models\Permission::where('capability_scope', 'buyer')
            ->where('is_assignable', true)
            ->where('is_active', true)
            ->firstOrFail();

        $this->actingAs($owner)->post(route('buyer.role-requests.store'), [
            'role_name' => 'procurement_officer',
            'display_name' => 'Procurement Officer',
            'description' => 'Handles day-to-day RFQ management.',
            'requested_permissions' => [$permission->name],
        ])->assertRedirect(route('buyer.role-requests.index'));

        $this->assertDatabaseHas('role_requests', [
            'account_id' => $owner->account->id,
            'role_name' => 'procurement_officer',
            'status' => 'pending',
        ]);
    }
}
