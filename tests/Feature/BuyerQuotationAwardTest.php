<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerQuotationAwardTest extends TestCase
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
            'lead_time_days'       => 14,
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

    public function test_buyer_can_view_compare_shortlist_and_award_a_quotation(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier@example.com');

        $rfq        = $this->makeOpenRfq($buyer);
        $quotation  = $this->makeSubmittedQuotation($rfq, $supplier, 5000);

        $this->actingAs($buyer)->get(route('buyer.quotations.index'))
            ->assertOk()
            ->assertSee('5,000.00');

        $this->actingAs($buyer)->get(route('buyer.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Award This Quotation');

        // The compare page shell renders client-side (localStorage-driven
        // fetch to compare.data) — the server-rendered shell shows the RFQ
        // context, not quotation figures; the data endpoint is what actually
        // carries the grand_total, so both are asserted separately here.
        $this->actingAs($buyer)->get(route('buyer.quotations.compare', $rfq))
            ->assertOk()
            ->assertSee('Science lab kits');

        $this->actingAs($buyer)->postJson(route('buyer.quotations.compare.data', $rfq), [
            'quotation_ids' => [$quotation->id],
        ])
            ->assertOk()
            ->assertJsonPath('commercial.rows.0.grand_total', 5000);

        // Shortlist
        $this->actingAs($buyer)->post(route('buyer.quotations.shortlist', $quotation))
            ->assertRedirect();

        $this->assertDatabaseHas('rfq_shortlists', ['quotation_id' => $quotation->id]);

        // Award
        $this->actingAs($buyer)->post(route('buyer.quotations.award', $quotation))
            ->assertRedirect();

        $this->assertDatabaseHas('awards', [
            'quotation_id' => $quotation->id,
            'status'       => 'pending_supplier_response',
        ]);

        $rfq->refresh();
        $this->assertSame('award_pending', $rfq->status);

        $award = Award::where('quotation_id', $quotation->id)->firstOrFail();

        $this->actingAs($buyer)->get(route('buyer.awards.index'))->assertOk()->assertSee('Science lab kits');
        $this->actingAs($buyer)->get(route('buyer.awards.show', $award))->assertOk()->assertSee('Waiting for the supplier to respond');
    }

    public function test_buyer_can_reject_a_quotation_with_a_reason(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier2@example.com');

        $rfq       = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)->post(route('buyer.quotations.reject', $quotation), [
            'reason' => 'Price too high for our budget.',
        ])->assertRedirect();

        $this->assertSame('rejected', $quotation->fresh()->status);
    }

    public function test_a_buyer_cannot_view_another_buyers_quotation_or_award(): void
    {
        $buyerA   = $this->makeActiveAccount('buyer', 'buyera@example.com');
        $buyerB   = $this->makeActiveAccount('buyer', 'buyerb@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier3@example.com');

        $rfq       = $this->makeOpenRfq($buyerA);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyerB)->get(route('buyer.quotations.show', $quotation))->assertForbidden();
        $this->actingAs($buyerB)->get(route('buyer.rfqs.show', $rfq))->assertForbidden();
    }

    public function test_empty_awards_and_purchase_order_indexes_render(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer4@example.com');

        $this->actingAs($buyer)->get(route('buyer.awards.index'))->assertOk()->assertSee('No awards yet');
        $this->actingAs($buyer)->get(route('buyer.purchase-orders.index'))->assertOk()->assertSee('No purchase orders yet');
    }
}
