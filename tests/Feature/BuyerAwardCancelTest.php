<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * "Undo Award" — the buyer-facing action that cancels a still-pending award
 * (Award.status = 'pending_supplier_response') before the supplier has
 * responded. Distinct from the supplier's own accept/reject: this is the
 * buyer pulling their own award decision back.
 */
class BuyerAwardCancelTest extends TestCase
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

    private function makeOpenRfq(User $buyer): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'              => 'Science lab kits',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items'              => [
                ['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    private function makeSubmittedQuotation(Rfq $rfq, User $supplier, float $total = 5000): Quotation
    {
        $quotation = Quotation::create([
            'quotation_number'     => 'QT-TEST-' . uniqid(),
            'rfq_id'               => $rfq->id,
            'supplier_account_id'  => $supplier->account->id,
            'submitted_by_user_id' => $supplier->id,
            'rfq_version_no'       => $rfq->current_version_no,
            'subtotal'             => $total,
            'grand_total'          => $total,
            'currency_code'        => 'USD',
            'status'               => 'submitted',
            'submitted_at'         => now(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'rfq_item_id'  => $rfq->items->first()->id,
            'item_name'    => 'Lab kit',
            'quantity'     => 20,
            'unit_price'   => $total / 20,
            'line_total'   => $total,
        ]);

        $rfq->increment('quotations_count');

        return $quotation->fresh(['items']);
    }

    public function test_buyer_can_cancel_a_pending_award_and_the_rfq_reopens(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-cancel@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-cancel@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotation))->assertRedirect();

        $award = Award::where('quotation_id', $quotation->id)->firstOrFail();
        $rfq->refresh();
        $this->assertSame('award_pending', $rfq->status);

        $this->actingAs($buyer)
            ->delete(route('buyer.quotations.award.cancel', $quotation), ['reason' => 'Changed our mind'])
            ->assertRedirect(route('buyer.quotations.show', $quotation));

        $award->refresh();
        $this->assertSame('cancelled', $award->status);
        $this->assertNotNull($award->cancelled_at);

        $rfq->refresh();
        $this->assertSame('open', $rfq->status);

        // Quotation status is untouched — award creation never promoted it.
        $this->assertSame('submitted', $quotation->fresh()->status);

        $this->assertTrue($quotation->activities()->where('activity_type', 'award_cancelled')->where('actor_role', 'buyer')->exists());

        // NOTE: awards.quotation_id is a unique index (Quotation::award()
        // docblock: "a quotation has at most one award"), so a cancelled
        // award permanently blocks ever awarding THIS SAME quotation again
        // — cancelling only frees the RFQ up to award a *different*
        // quotation, confirmed below. That's an existing schema constraint
        // this feature inherits, not something introduced here.
        $otherSupplier = $this->makeActiveAccount('supplier', 'supplier-cancel-b@example.com');
        $otherQuotation = $this->makeSubmittedQuotation($rfq, $otherSupplier, 4000);
        $this->actingAs($buyer)->post(route('buyer.quotations.award', $otherQuotation))->assertRedirect();
        $this->assertDatabaseHas('awards', ['quotation_id' => $otherQuotation->id, 'status' => 'pending_supplier_response']);
    }

    public function test_award_cancel_is_forbidden_once_the_supplier_has_already_responded(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-cancel2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-cancel2@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotation))->assertRedirect();
        $award = Award::where('quotation_id', $quotation->id)->firstOrFail();

        app(AwardResponseService::class)->accept($award);

        $this->actingAs($buyer)
            ->delete(route('buyer.quotations.award.cancel', $quotation))
            ->assertForbidden();

        $this->assertSame('accepted', $award->fresh()->status);
    }

    public function test_a_different_buyer_cannot_cancel_someone_elses_award(): void
    {
        $buyerA = $this->makeActiveAccount('buyer', 'buyer-cancel3@example.com');
        $buyerB = $this->makeActiveAccount('buyer', 'buyer-cancel3b@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-cancel3@example.com');

        $rfq = $this->makeOpenRfq($buyerA);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyerA)->post(route('buyer.quotations.award', $quotation))->assertRedirect();

        $this->actingAs($buyerB)
            ->delete(route('buyer.quotations.award.cancel', $quotation))
            ->assertForbidden();
    }

    public function test_cancel_award_404s_when_the_quotation_has_no_award(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-cancel4@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-cancel4@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)
            ->delete(route('buyer.quotations.award.cancel', $quotation))
            ->assertNotFound();
    }

    public function test_statistics_endpoint_returns_activity_summary_for_buyer(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-cancel5@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-cancel5@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotation))->assertRedirect();

        $response = $this->actingAs($buyer)
            ->getJson(route('buyer.quotations.statistics', $quotation))
            ->assertOk();

        $response->assertJson(['quotation_id' => $quotation->id, 'status' => 'submitted']);
        $this->assertNotEmpty($response->json('last_activity_label'));
    }

    public function test_index_defaults_to_submitted_only_and_shows_award_or_undo_award_in_the_action_column(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-index@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-index@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $submitted = $this->makeSubmittedQuotation($rfq, $supplier, 5000);

        $rejectedSupplier = $this->makeActiveAccount('supplier', 'supplier-index-b@example.com');
        $rejected = $this->makeSubmittedQuotation($rfq, $rejectedSupplier, 3000);
        $rejected->update(['status' => 'rejected']);

        // Default view (no ?status=) shows only 'submitted' and offers Award.
        $response = $this->actingAs($buyer)->get(route('buyer.quotations.index'));
        $response->assertOk()->assertSee($submitted->quotation_number)->assertDontSee($rejected->quotation_number);
        $response->assertSee('Award');

        // Explicit ?status= (All Statuses) shows everything again.
        $this->actingAs($buyer)->get(route('buyer.quotations.index', ['status' => '']))
            ->assertOk()->assertSee($submitted->quotation_number)->assertSee($rejected->quotation_number);

        // Once awarded (pending), the row shows Undo Award instead of Award.
        $this->actingAs($buyer)->post(route('buyer.quotations.award', $submitted))->assertRedirect();
        $this->actingAs($buyer)->get(route('buyer.quotations.index'))
            ->assertOk()->assertSee('Undo Award');
    }

    public function test_index_ajax_request_returns_table_html_and_status_counts_for_the_live_filter_bar(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-ajax@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-ajax@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $submitted = $this->makeSubmittedQuotation($rfq, $supplier, 5000);
        $rejected = $this->makeSubmittedQuotation($rfq, $this->makeActiveAccount('supplier', 'supplier-ajax-b@example.com'), 2000);
        $rejected->update(['status' => 'rejected']);

        // Matches the real live-filter bar's fetch() call, which signals
        // AJAX via X-Requested-With (QuotationController::index() checks
        // $request->ajax(), not the Accept header getJson() alone sends).
        $response = $this->actingAs($buyer)
            ->get(route('buyer.quotations.index', ['status' => ['submitted', 'rejected']]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $response->assertJsonStructure(['table_html', 'status_counts']);
        $this->assertStringContainsString($submitted->quotation_number, $response->json('table_html'));
        $this->assertStringContainsString($rejected->quotation_number, $response->json('table_html'));
        $this->assertSame(1, $response->json('status_counts.submitted'));
        $this->assertSame(1, $response->json('status_counts.rejected'));
    }

    public function test_multi_select_status_filter_narrows_the_index_to_only_the_checked_statuses(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer-multi@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier-multi@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $awarded = $this->makeSubmittedQuotation($rfq, $supplier, 5000);
        $awarded->update(['status' => 'awarded']);
        $rejected = $this->makeSubmittedQuotation($rfq, $this->makeActiveAccount('supplier', 'supplier-multi-b@example.com'), 2000);
        $rejected->update(['status' => 'rejected']);
        $shortlisted = $this->makeSubmittedQuotation($rfq, $this->makeActiveAccount('supplier', 'supplier-multi-c@example.com'), 3000);
        $shortlisted->update(['status' => 'shortlisted']);

        $response = $this->actingAs($buyer)->get(route('buyer.quotations.index', ['status' => ['awarded', 'shortlisted']]));

        $response->assertOk()
            ->assertSee($awarded->quotation_number)
            ->assertSee($shortlisted->quotation_number)
            ->assertDontSee($rejected->quotation_number);
    }
}
