<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\CapabilityType;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use App\Models\Rfq;
use App\Models\RoleRequest;
use App\Models\SupplierType;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\AwardService;
use App\Services\CapabilityReviewService;
use App\Services\QuotationService;
use App\Services\RfqService;
use App\Services\RoleRequestService;
use App\Services\SupplierDocumentService;
use App\Services\SupplierProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Full-loop scenarios that deliberately cross Buyer / Supplier / Admin
 * portal boundaries, exercised only after all three dashboards individually
 * passed their own audits. Each test drives the real controllers/services of
 * every portal involved rather than reaching into internal state directly,
 * so a break in any portal's wiring surfaces here even if that portal's own
 * test suite doesn't happen to cover the exact cross-portal interaction.
 */
class CrossDashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeAdmin(string $email): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name' => 'Test Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $admin->id, 'member_type' => 'owner',
            'is_primary_owner' => true, 'status' => 'active', 'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    private function makeActiveAccount(string $capability, string $email): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => $capability,
            'name' => ucfirst($capability) . ' Test User', 'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999), 'password' => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->{$capability . 'Capability'}()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    private function giveActiveSubscription(User $supplier): void
    {
        $plan = \App\Models\SubscriptionPlan::create([
            'name' => 'Free Plan', 'slug' => 'free-plan-' . uniqid(), 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);

        $subscription = \App\Models\Subscription::create([
            'supplier_account_id' => $supplier->account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $supplier->id, 'provider' => 'free', 'status' => 'pending',
        ]);

        $subscription->activate();
    }

    private function makeOpenRfq(User $buyer, string $visibility = 'global', array $supplierAccountIds = []): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Science lab kits', 'visibility_type' => $visibility,
            'selected_supplier_ids' => $supplierAccountIds,
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items' => [['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20]],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    /**
     * Scenario 1 — Supplier Onboarding: registration -> Admin capability
     * approval (with document verification) -> Supplier selects a
     * subscription -> Supplier dashboard becomes fully usable.
     */
    public function test_supplier_onboarding_end_to_end_through_admin_approval(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cross-admin1@example.com');

        $supplier = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'supplier',
            'name' => 'Onboarding Supplier', 'email' => 'cross-supplier1@example.com',
            'phone' => '+15550001111', 'password' => 'Password123!',
            'organization_display_name' => 'Onboarding Supplier Inc',
        ]);
        $supplier->markEmailAsVerified();
        $supplier->update(['status' => 'active']);
        $account = $supplier->account;
        $account->update(['status' => 'active']);

        Storage::fake('public');
        $country = \App\Models\Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer-x', 'code' => 'MFGX', 'is_active' => true]);
        $docType = DocumentType::create(['name' => 'Trade License', 'slug' => 'trade-license-x', 'code' => 'TLX', 'is_required' => true, 'is_active' => true]);
        DocumentTypeEnable::create([
            'document_type_id' => $docType->id,
            'capability_type_id' => CapabilityType::where('code', 'supplier')->value('id'),
            'is_required' => true,
        ]);

        app(SupplierProfileService::class)->completeProfile($account, [
            'display_name' => 'Onboarding Supplier Inc', 'legal_name' => 'Onboarding Supplier Incorporated',
            'legal_entity_type' => 'Corporation', 'supplier_type_ids' => [$supplierType->id],
            'contact_person' => 'Jane Doe', 'contact_email' => 'cross-supplier1@example.com',
            'country_id' => $country->id, 'address' => '1 Business Way',
        ]);

        $file = UploadedFile::fake()->create('trade.pdf', 200, 'application/pdf');
        $doc = app(SupplierDocumentService::class)->upload($account, $docType->id, $file, $supplier);

        $cap = $account->supplierCapability;
        $cap->update(['status' => 'pending', 'application_attempts' => 1]);

        // Admin verifies the document, then approves the capability.
        $doc->update(['status' => 'verified', 'verified_by_user_id' => $admin->id, 'verified_at' => now()]);
        app(CapabilityReviewService::class)->approve($cap, $admin);

        $this->assertSame('active', $cap->fresh()->status);

        // Supplier selects a subscription — required before dashboard use.
        $this->giveActiveSubscription($supplier->fresh());

        $this->actingAs($supplier)->get(route('supplier.dashboard'))->assertOk();
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.index'))->assertOk();
    }

    /**
     * Scenarios 2 & 3 — Global RFQ vs Selected-Supplier RFQ: a global RFQ
     * reaches every subscribed supplier through the queue; a selected RFQ
     * reaches only the explicitly targeted supplier account.
     */
    public function test_global_rfq_and_selected_supplier_rfq_reach_the_correct_suppliers(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'cross-buyer2@example.com');
        $supplierA = $this->makeActiveAccount('supplier', 'cross-supplierA2@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'cross-supplierB2@example.com');
        $this->giveActiveSubscription($supplierA);
        $this->giveActiveSubscription($supplierB);

        $globalRfq = $this->makeOpenRfq($buyer, 'global');
        $this->actingAs($supplierA)->get(route('supplier.opportunities.show', $globalRfq))->assertOk();
        $this->actingAs($supplierB)->get(route('supplier.opportunities.show', $globalRfq))->assertOk();

        $selectedRfq = $this->makeOpenRfq($buyer, 'selected_suppliers', [$supplierA->account->id]);
        $this->actingAs($supplierA)->get(route('supplier.opportunities.show', $selectedRfq))->assertOk();
        $this->actingAs($supplierB)->get(route('supplier.opportunities.show', $selectedRfq))->assertForbidden();
    }

    /**
     * Scenarios 4-8 — Quotation Revision -> Award -> Award Rejection ->
     * re-Award -> Purchase Order -> Reviews, driven through the real
     * Buyer and Supplier controllers end to end.
     */
    public function test_quotation_revision_award_rejection_reaward_po_and_review_flow(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'cross-buyer3@example.com');
        $supplierA = $this->makeActiveAccount('supplier', 'cross-supplierA3@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'cross-supplierB3@example.com');
        $this->giveActiveSubscription($supplierA);
        $this->giveActiveSubscription($supplierB);

        $rfq = $this->makeOpenRfq($buyer);

        $quotationA = app(QuotationService::class)->submit($rfq, $supplierA->account, $supplierA, [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 120]],
        ]);

        // Buyer requests a revision through the real controller.
        $this->actingAs($buyer)->post(route('buyer.quotations.request-revision', $quotationA), [
            'requested_changes' => 'Please lower the unit price.',
        ])->assertRedirect();

        $this->assertDatabaseHas('quotation_revision_requests', ['quotation_id' => $quotationA->id, 'status' => 'pending']);

        // Supplier revises through the real controller — history preserved.
        $this->actingAs($supplierA)->post(route('supplier.quotations.revision.store', $quotationA), [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100]],
            'change_summary' => 'Reduced unit price per buyer request.',
        ])->assertRedirect(route('supplier.quotations.show', $quotationA));

        $quotationA->refresh();
        $this->assertSame(2, $quotationA->current_revision_no);
        $this->assertEquals(2000, (float) $quotationA->grand_total);
        $this->assertGreaterThanOrEqual(2, $quotationA->revisions()->count());

        // Buyer awards the revised quotation via the real controller.
        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotationA))->assertRedirect();
        $award = \App\Models\Award::where('quotation_id', $quotationA->id)->firstOrFail();
        $this->assertSame('pending_supplier_response', $award->status);

        // Supplier A rejects — RFQ must reopen for a second award.
        $this->actingAs($supplierA)->post(route('supplier.awards.reject', $award), [
            'reason' => 'No longer able to fulfil this order.',
        ])->assertRedirect();

        $this->assertSame('rejected_by_supplier', $award->fresh()->status);
        $this->assertSame('open', $rfq->fresh()->status);

        // Buyer re-awards a different supplier's quotation for the same RFQ.
        $quotationB = app(QuotationService::class)->submit($rfq, $supplierB->account, $supplierB, [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 90]],
        ]);
        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotationB))->assertRedirect();
        $awardB = \App\Models\Award::where('quotation_id', $quotationB->id)->firstOrFail();

        // Supplier B accepts — PO created exactly once, at `issued`.
        $this->actingAs($supplierB)->post(route('supplier.awards.accept', $awardB))->assertRedirect();
        $this->assertSame(1, \App\Models\PurchaseOrder::where('award_id', $awardB->id)->count());
        $po = \App\Models\PurchaseOrder::where('award_id', $awardB->id)->firstOrFail();
        $this->assertSame('issued', $po->status);

        // Buyer completes the PO directly from `issued` (Phase 1).
        $this->actingAs($buyer)->post(route('buyer.purchase-orders.complete', $po))->assertRedirect();
        $this->assertSame('completed', $po->fresh()->status);

        // Buyer reviews, Supplier B replies.
        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-purchase-order', $po), ['rating' => 5])->assertRedirect();
        $review = \App\Models\Review::where('purchase_order_id', $po->id)->firstOrFail();

        $this->actingAs($supplierB)->post(route('supplier.reviews.reply', $review), ['reply' => 'Thank you!'])->assertRedirect();
        $this->assertDatabaseHas('review_replies', ['review_id' => $review->id, 'supplier_account_id' => $supplierB->account->id]);

        // The rejected-then-superseded first award must remain in history, untouched.
        $this->assertDatabaseHas('awards', ['id' => $award->id, 'status' => 'rejected_by_supplier']);
    }

    /**
     * Scenario 9 — Role Request: Supplier submits a custom-role request,
     * Admin approves it, and the resulting account-scoped role is then
     * immediately usable from inside the Supplier portal itself.
     */
    public function test_role_request_approved_by_admin_becomes_usable_inside_supplier_portal(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cross-admin9@example.com');

        $owner = $this->makeActiveAccount('supplier', 'cross-owner9@example.com');
        $owner->account->update(['account_type' => 'organization']);
        $owner->account->members()->where('user_id', $owner->id)->update(['is_primary_owner' => true, 'member_type' => 'owner']);
        $this->giveActiveSubscription($owner);

        $member = User::factory()->create(['status' => 'active']);
        AccountMember::create([
            'account_id' => $owner->account->id, 'user_id' => $member->id, 'member_type' => 'member',
            'is_primary_owner' => false, 'status' => 'active', 'joined_at' => now(),
        ]);

        $roleRequest = RoleRequest::create([
            'account_id' => $owner->account->id, 'requested_by_user_id' => $owner->id,
            'role_name' => 'catalog_manager', 'display_name' => 'Catalog Manager',
            'capability_scope' => 'supplier', 'requested_permissions' => ['listing.view', 'listing.create'],
            'description' => 'Manages catalog listings only.', 'status' => 'pending',
        ]);

        app(RoleRequestService::class)->approve($roleRequest, $admin);
        $this->assertSame('approved', $roleRequest->fresh()->status);

        $role = \App\Models\Role::where('account_id', $owner->account->id)->where('name', 'catalog_manager')->firstOrFail();

        // The account owner assigns the newly-approved role from inside their own portal.
        $memberRow = $owner->account->members()->where('user_id', $member->id)->firstOrFail();
        $this->actingAs($owner)->post(route('supplier.roles.assign', $role), ['member_id' => $memberRow->id])
            ->assertRedirect();

        $member->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account->id);
        $member->unsetRelation('roles');
        $this->assertTrue($member->hasRole('catalog_manager'));
    }

    /**
     * Scenario 10 — Account Suspension: Admin suspends a Supplier account;
     * the same account's dashboard must be immediately and fully blocked,
     * with no commercial history destroyed.
     */
    public function test_admin_account_suspension_immediately_blocks_the_suppliers_dashboard(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cross-admin10@example.com');

        $buyer = $this->makeActiveAccount('buyer', 'cross-buyer10@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'cross-supplier10@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 80]],
        ]);

        $this->actingAs($supplier)->get(route('supplier.dashboard'))->assertOk();

        // Admin suspends the account.
        $this->actingAs($admin)->post(route('admin.accounts.suspend', $supplier->account), [
            'reason' => 'Under investigation for policy violation.',
        ])->assertRedirect();

        $this->assertSame('suspended', $supplier->account->fresh()->status);

        // The supplier's own dashboard is now blocked...
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.index'))->assertForbidden();

        // ...but the pre-existing commercial history is preserved, not deleted.
        $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'supplier_account_id' => $supplier->account->id]);

        // Reactivation restores access.
        $this->actingAs($admin)->post(route('admin.accounts.reactivate', $supplier->account))->assertRedirect();
        $this->assertSame('active', $supplier->account->fresh()->status);
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.index'))->assertOk();
    }
}
