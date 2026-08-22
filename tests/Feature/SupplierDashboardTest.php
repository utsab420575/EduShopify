<?php

namespace Tests\Feature;

use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\AwardService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Exercises the real Controller/Blade supplier backend (routes/backend/supplier.php),
 * not the orphaned pre-rebuild Livewire components that used to live under
 * app/Livewire/Supplier — those were never routed and have been removed.
 */
class SupplierDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveAccount(string $capability, string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => $capability,
            'name'         => ucfirst($capability) . ' Test User',
            'email'        => $email,
            'phone'        => '+15550009999',
            'password'     => 'Password123!',
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

    private function giveActiveSubscription(User $supplier): Subscription
    {
        $plan = SubscriptionPlan::create([
            'name'         => 'Free Plan',
            'slug'         => 'free-plan-' . uniqid(),
            'billing_type' => 'free',
            'price'        => 0,
            'currency_code' => 'USD',
            'is_free'      => true,
            'is_active'    => true,
            'max_active_listings'    => 10,
            'max_monthly_quotations' => 50,
            'rfq_delay_minutes'      => 0,
        ]);

        $subscription = Subscription::create([
            'supplier_account_id' => $supplier->account->id,
            'plan_id'              => $plan->id,
            'selected_by_user_id'  => $supplier->id,
            'provider'             => 'free',
            'status'               => 'pending',
        ]);

        $subscription->activate();

        return $subscription->fresh();
    }

    private function makeOpenRfq(User $buyer, string $visibility = 'global', array $supplierAccountIds = []): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'                  => 'Science lab kits',
            'visibility_type'        => $visibility,
            'selected_supplier_ids'  => $supplierAccountIds,
            'quotation_deadline'     => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items'                  => [
                ['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    public function test_supplier_without_subscription_is_redirected_to_pricing_from_the_whole_panel(): void
    {
        $supplier = $this->makeActiveAccount('supplier', 'supplier1@example.com');

        // Dashboard root stays reachable (shows the "choose a plan" banner)...
        $this->actingAs($supplier)->get(route('supplier.dashboard'))->assertOk();

        // ...but every functional module is gated behind an active subscription
        // (EnsureSupplierHasPlan — previously defined but never wired into the route file).
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.index'))
            ->assertRedirect(route('supplier.pricing'));
        $this->actingAs($supplier)->get(route('supplier.opportunities.index'))
            ->assertRedirect(route('supplier.pricing'));
    }

    public function test_supplier_with_active_subscription_can_view_dashboard_and_listings(): void
    {
        $supplier = $this->makeActiveAccount('supplier', 'supplier2@example.com');
        $this->giveActiveSubscription($supplier);

        $this->actingAs($supplier)->get(route('supplier.dashboard'))->assertOk();
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.index'))->assertOk();
        $this->actingAs($supplier)->get(route('supplier.catalog.listings.create'))->assertOk();
    }

    public function test_supplier_can_create_and_submit_a_product_listing_for_approval(): void
    {
        $supplier = $this->makeActiveAccount('supplier', 'supplier3@example.com');
        $this->giveActiveSubscription($supplier);

        $response = $this->actingAs($supplier)->post(route('supplier.catalog.listings.store'), [
            'listing_type'       => 'product',
            'name'               => 'Microscope Kit',
            'main_category_id'   => $this->makeCategory()->id,
            'pricing_type'       => 'quote_only',
            'currency_code'      => 'USD',
            'stock_status'       => 'in_stock',
        ]);

        $listing = \App\Models\Listing::where('name', 'Microscope Kit')->firstOrFail();
        $response->assertRedirect(route('supplier.catalog.listings.show', $listing));
        $this->assertSame('draft', $listing->approval_status);

        // Regression: submit() used to write the invalid enum literal
        // 'pending_approval' into listings.approval_status (real enum: pending),
        // which threw a QueryException on every submission.
        $this->actingAs($supplier)->post(route('supplier.catalog.listings.submit', $listing))
            ->assertRedirect(route('supplier.catalog.listings.show', $listing));
        $this->assertSame('pending', $listing->fresh()->approval_status);
    }

    public function test_supplier_can_create_a_service_listing_without_crashing(): void
    {
        $supplier = $this->makeActiveAccount('supplier', 'supplier3b@example.com');
        $this->giveActiveSubscription($supplier);

        // Regression: service listing creation always crashed — the controller
        // defaulted service_details.service_mode to 'both', which isn't a valid
        // value in the real enum (onsite/remote/hybrid), and the create form
        // never even collected the field.
        $response = $this->actingAs($supplier)->post(route('supplier.catalog.listings.store'), [
            'listing_type'      => 'service',
            'name'              => 'Curriculum Consulting',
            'main_category_id'  => $this->makeCategory()->id,
            'pricing_type'      => 'quote_only',
            'currency_code'     => 'USD',
        ]);

        $listing = \App\Models\Listing::where('name', 'Curriculum Consulting')->firstOrFail();
        $response->assertRedirect(route('supplier.catalog.listings.show', $listing));
        $this->assertNotNull($listing->serviceDetail);
    }

    private function makeCategory(): \App\Models\Category
    {
        return \App\Models\Category::create([
            'name' => 'Lab Equipment ' . uniqid(),
            'slug' => 'lab-equipment-' . uniqid(),
            'type' => 'product',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_publishing_a_global_rfq_queues_it_for_eligible_suppliers_only(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer1@example.com');

        $eligibleSupplier = $this->makeActiveAccount('supplier', 'supplier4@example.com');
        $this->giveActiveSubscription($eligibleSupplier);

        $noSubSupplier = $this->makeActiveAccount('supplier', 'supplier5@example.com');

        $rfq = $this->makeOpenRfq($buyer);

        $this->assertDatabaseHas('rfq_supplier_queue', [
            'rfq_id'              => $rfq->id,
            'supplier_account_id' => $eligibleSupplier->account->id,
            'eligibility_status'  => 'eligible',
        ]);

        $this->assertDatabaseHas('rfq_supplier_queue', [
            'rfq_id'              => $rfq->id,
            'supplier_account_id' => $noSubSupplier->account->id,
            'eligibility_status'  => 'no_subscription',
        ]);

        $this->actingAs($eligibleSupplier)->get(route('supplier.opportunities.index'))
            ->assertOk()->assertSee('Science lab kits');

        $this->actingAs($eligibleSupplier)->get(route('supplier.opportunities.show', $rfq))
            ->assertOk()->assertSee('Science lab kits');

        // The unsubscribed supplier never reaches the opportunity page at all —
        // EnsureSupplierHasPlan redirects to pricing before the policy check runs.
        $this->actingAs($noSubSupplier)->get(route('supplier.opportunities.show', $rfq))
            ->assertRedirect(route('supplier.pricing'));
    }

    public function test_a_queued_but_ineligible_supplier_cannot_view_rfq_opportunity_detail(): void
    {
        // Regression: OpportunityController::show()/askQuestion() never called
        // RfqPolicy::viewAsOpportunity()/askQuestion() — any active supplier
        // (subscribed or not, queued or not) could load full RFQ detail for
        // any RFQ id, leaking buyer-private data before eligibility permitted it.
        $buyer = $this->makeActiveAccount('buyer', 'buyer1b@example.com');

        $subscribedButNotTargeted = $this->makeActiveAccount('supplier', 'supplier4b@example.com');
        $this->giveActiveSubscription($subscribedButNotTargeted);

        // A selected-supplier RFQ that does NOT target this supplier.
        $otherSupplier = $this->makeActiveAccount('supplier', 'supplier4c@example.com');
        $this->giveActiveSubscription($otherSupplier);
        $rfq = $this->makeOpenRfq($buyer, 'selected_suppliers', [$otherSupplier->account->id]);

        $this->actingAs($subscribedButNotTargeted)->get(route('supplier.opportunities.show', $rfq))
            ->assertForbidden();

        $this->actingAs($subscribedButNotTargeted)->post(route('supplier.opportunities.questions.store', $rfq), [
            'question' => 'Can you clarify the spec?',
        ])->assertForbidden();
    }

    public function test_full_loop_supplier_quotes_buyer_awards_supplier_accepts_and_buyer_completes(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier6@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);

        // Supplier submits a quotation.
        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $this->assertSame('submitted', $quotation->status);
        $this->assertEquals(2000, (float) $quotation->grand_total);

        $this->actingAs($supplier)->get(route('supplier.quotations.show', $quotation))
            ->assertOk()->assertSee('2,000.00');

        // Buyer awards it.
        $award = app(AwardService::class)->create($quotation, $buyer);
        $this->assertSame('pending_supplier_response', $award->status);

        $this->actingAs($supplier)->get(route('supplier.awards.show', $award))
            ->assertOk()->assertSee('accept or reject this award');

        // Supplier accepts through the real controller action — this creates a
        // Purchase Order exactly once, directly at `issued` (Phase 1).
        $this->actingAs($supplier)->post(route('supplier.awards.accept', $award))
            ->assertRedirect(route('supplier.awards.show', $award));

        $award->refresh();
        $this->assertSame('accepted', $award->status);
        $this->assertSame('awarded', $rfq->fresh()->status);
        $this->assertSame('awarded', $quotation->fresh()->status);

        $po = \App\Models\PurchaseOrder::where('award_id', $award->id)->firstOrFail();
        $this->assertSame('issued', $po->status);
        $this->assertSame(1, $po->items()->count());

        // Phase 1: no legacy confirmed/in_progress/ready_for_delivery/delivered
        // pipeline. The supplier can no longer complete the PO themselves either
        // (that route was removed — completion is buyer-authorized only).
        $this->actingAs($supplier)->post('/supplier/purchase-orders/' . $po->id . '/complete')
            ->assertNotFound();

        // Buyer completes it directly from `issued`.
        $this->actingAs($buyer)->post(route('buyer.purchase-orders.complete', $po))
            ->assertRedirect();

        $this->assertSame('completed', $po->fresh()->status);

        // Buyer can now review the completed purchase order, and the supplier can reply.
        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-purchase-order', $po), [
            'rating' => 5,
        ])->assertRedirect();

        $review = \App\Models\Review::where('purchase_order_id', $po->id)->firstOrFail();
        $this->assertSame('purchase_experience', $review->review_context);

        $this->actingAs($supplier)->post(route('supplier.reviews.reply', $review), [
            'reply' => 'Thank you for the order!',
        ])->assertRedirect(route('supplier.reviews.index'));

        $this->assertDatabaseHas('review_replies', ['review_id' => $review->id, 'supplier_account_id' => $supplier->account->id]);
    }

    public function test_supplier_can_reject_an_award_and_rfq_reopens_for_a_new_award(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier7@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq       = $this->makeOpenRfq($buyer);
        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 50]],
        ]);
        $award = app(AwardService::class)->create($quotation, $buyer);

        $this->actingAs($supplier)->post(route('supplier.awards.reject', $award), [
            'reason' => 'Cannot meet the deadline.',
        ])->assertRedirect(route('supplier.awards.show', $award));

        $this->assertSame('rejected_by_supplier', $award->fresh()->status);
        $this->assertSame('open', $rfq->fresh()->status);

        // Buyer can award a second attempt against the same quotation-eligible RFQ.
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($buyer)->allows('award', $quotation->fresh()->rfq->quotations()->first()));
    }

    public function test_supplier_messaging_and_tickets(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer4@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier8@example.com');
        $this->giveActiveSubscription($supplier);

        // Messaging — buyer starts, supplier replies.
        $this->actingAs($buyer)->post(route('buyer.suppliers.message', $supplier->account))
            ->assertRedirect();

        $conversation = \App\Models\Conversation::firstOrFail();

        $this->actingAs($supplier)->get(route('supplier.messages.index'))->assertOk();

        $this->actingAs($supplier)->post(route('supplier.messages.store', $conversation), [
            'body' => 'Sure, happy to help.',
        ])->assertRedirect(route('supplier.messages.show', $conversation));

        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'sender_account_id' => $supplier->account->id]);

        // Tickets — supplier opens one.
        $this->actingAs($supplier)->post(route('supplier.tickets.store'), [
            'subject'  => 'Payout question',
            'category' => 'billing',
            'priority' => 'normal',
            'message'  => 'When are payouts processed?',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', ['account_id' => $supplier->account->id, 'subject' => 'Payout question']);
    }

    public function test_a_supplier_cannot_view_another_suppliers_quotation_award_or_conversation(): void
    {
        $buyer     = $this->makeActiveAccount('buyer', 'buyer5@example.com');
        $supplierA = $this->makeActiveAccount('supplier', 'supplier9@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'supplier10@example.com');
        $this->giveActiveSubscription($supplierA);
        $this->giveActiveSubscription($supplierB);

        $rfq       = $this->makeOpenRfq($buyer);
        $quotation = app(QuotationService::class)->submit($rfq, $supplierA->account, $supplierA, [
            'items' => [['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 50]],
        ]);
        $award = app(AwardService::class)->create($quotation, $buyer);

        $this->actingAs($supplierB)->get(route('supplier.quotations.show', $quotation))->assertForbidden();
        $this->actingAs($supplierB)->get(route('supplier.awards.show', $award))->assertForbidden();

        // Regression: MessageController had no authorize() calls at all — any
        // supplier could view/reply to any conversation by guessing its id.
        $this->actingAs($buyer)->post(route('buyer.suppliers.message', $supplierA->account))->assertRedirect();
        $conversation = \App\Models\Conversation::firstOrFail();

        $this->actingAs($supplierB)->get(route('supplier.messages.show', $conversation))->assertForbidden();
        $this->actingAs($supplierB)->post(route('supplier.messages.store', $conversation), ['body' => 'hi'])->assertForbidden();
    }

    public function test_role_cannot_be_assigned_across_accounts(): void
    {
        // Regression: RoleController::assign() took a raw global user_id with no
        // membership/role-ownership scoping — any org member could assign a
        // role belonging to a DIFFERENT account to an arbitrary user_id.
        $ownerA = $this->makeOrganizationOwner('supplier', 'roleowner-a@example.com');
        $ownerB = $this->makeOrganizationOwner('supplier', 'roleowner-b@example.com');
        $this->giveActiveSubscription($ownerA);
        $this->giveActiveSubscription($ownerB);

        $roleA = \App\Models\Role::create([
            'account_id' => $ownerA->account->id,
            'name' => 'Custom Role A ' . uniqid(),
            'guard_name' => 'web',
            'capability_scope' => 'supplier',
            'is_active' => true,
        ]);

        // ownerB tries to assign account A's custom role to themselves via B's portal.
        $memberB = $ownerB->account->members()->where('user_id', $ownerB->id)->firstOrFail();

        $this->actingAs($ownerB)->post(route('supplier.roles.assign', $roleA), [
            'member_id' => $memberB->id,
        ])->assertForbidden();
    }

    private function makeOrganizationOwner(string $capability, string $email): User
    {
        $user = $this->makeActiveAccount($capability, $email);
        $account = $user->account;
        $account->update(['account_type' => 'organization']);
        $account->members()->where('user_id', $user->id)->update(['is_primary_owner' => true, 'member_type' => 'owner']);

        return $user->fresh();
    }
}
