<?php

namespace Tests\Feature;

use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * "Undo Submit" — the supplier-facing action that pulls a submitted
 * quotation back to draft (distinct from Withdraw, which is terminal) —
 * plus the quotation Statistics JSON endpoint that powers the index page's
 * shared modal and the show page's Activity tab.
 */
class QuotationUndoSubmitAndStatisticsTest extends TestCase
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

    private function makeOpenRfq(User $buyer): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'                  => 'Science lab kits',
            'visibility_type'        => 'global',
            'quotation_deadline'     => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items'                  => [
                ['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    public function test_undo_submit_reverts_a_submitted_quotation_to_draft_and_logs_activity(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-undo@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-undo@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $this->assertSame('submitted', $quotation->status);
        $this->assertNotNull($quotation->submitted_at);
        $rfq->refresh();
        $this->assertSame(1, $rfq->quotations_count);

        $this->actingAs($supplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertRedirect(route('supplier.quotations.edit', $quotation));

        $quotation->refresh();
        $rfq->refresh();

        $this->assertSame('draft', $quotation->status);
        $this->assertNull($quotation->submitted_at);
        $this->assertSame(0, $quotation->current_revision_no);
        $this->assertSame(0, $rfq->quotations_count);
        $this->assertTrue($quotation->activities()->where('activity_type', 'unsubmitted')->exists());
        // The revision snapshot created by the first submit must be gone —
        // otherwise resubmitting collides with quotation_revisions'
        // (quotation_id, revision_no) unique index (regression: this used
        // to throw a UniqueConstraintViolationException on '1-1').
        $this->assertSame(0, $quotation->revisions()->count());

        $queueRow = RfqSupplierQueue::where('rfq_id', $rfq->id)
            ->where('supplier_account_id', $supplier->account->id)
            ->first();
        $this->assertNotNull($queueRow);
        $this->assertSame('seen', $queueRow->status);

        // Now editable again and can be resubmitted, unlike a withdrawn quotation.
        $this->actingAs($supplier)->get(route('supplier.quotations.edit', $quotation))->assertOk();

        $this->actingAs($supplier)
            ->post(route('supplier.quotations.submit', $quotation))
            ->assertRedirect(route('supplier.quotations.show', $quotation));

        $quotation->refresh();
        $this->assertSame('submitted', $quotation->status);
        $this->assertTrue($quotation->activities()->where('activity_type', 'submitted')->where('actor_role', 'supplier')->exists());
    }

    public function test_undo_submit_is_forbidden_once_a_quotation_is_awarded(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-undo2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-undo2@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);
        $quotation->update(['status' => 'awarded']);

        $this->actingAs($supplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertForbidden();

        $this->assertSame('awarded', $quotation->fresh()->status);
    }

    public function test_undo_submit_is_allowed_if_award_was_cancelled_by_buyer(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-undo-cancel@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-undo-cancel@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        // Buyer awards the quotation
        $award = app(\App\Services\AwardService::class)->create($quotation, $buyer);

        // While award is pending response, supplier cannot undo submit
        $this->actingAs($supplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertForbidden();

        // Buyer cancels the award
        app(\App\Services\AwardService::class)->cancel($award, 'Changed requirements');

        // Now supplier CAN undo submit
        $this->assertTrue($supplier->fresh()->can('undoSubmit', $quotation->fresh()));

        $this->actingAs($supplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertRedirect(route('supplier.quotations.edit', $quotation));

        $this->assertSame('draft', $quotation->fresh()->status);
    }

    public function test_undo_submit_is_forbidden_for_a_quotation_still_in_draft(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-undo3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-undo3@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->saveDraft($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $this->actingAs($supplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertForbidden();
    }

    public function test_a_different_suppliers_user_cannot_undo_submit_someone_elses_quotation(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-undo4@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-undo4@example.com');
        $this->giveActiveSubscription($supplier);
        $otherSupplier = $this->makeActiveAccount('supplier', 'supplier-undo4b@example.com');
        $this->giveActiveSubscription($otherSupplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $this->actingAs($otherSupplier)
            ->post(route('supplier.quotations.undo-submit', $quotation))
            ->assertForbidden();
    }

    public function test_statistics_endpoint_returns_activity_summary_json(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-stats@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-stats@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $response = $this->actingAs($supplier)
            ->getJson(route('supplier.quotations.statistics', $quotation))
            ->assertOk();

        $response->assertJson([
            'quotation_id' => $quotation->id,
            'status' => 'submitted',
            'viewed_by_buyer' => false,
            'messages_count' => 0,
        ]);
        $response->assertJsonStructure(['recent_activities' => [['label', 'icon', 'color_class', 'created_at_human']]]);
        $this->assertSame('Quotation submitted', $response->json('last_activity_label'));
    }

    public function test_the_index_and_show_pages_render_the_new_action_column_and_tabs(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-ui@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-ui@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = $this->makeOpenRfq($buyer);
        $rfq->refresh();

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 100],
            ],
        ]);

        $this->actingAs($supplier)->get(route('supplier.quotations.index'))
            ->assertOk()->assertSee('Undo Submit')->assertSee('Statistics');

        $this->actingAs($supplier)->get(route('supplier.quotations.show', $quotation))
            ->assertOk()->assertSee('Statistics')->assertSee('Undo Submit');
    }
}
