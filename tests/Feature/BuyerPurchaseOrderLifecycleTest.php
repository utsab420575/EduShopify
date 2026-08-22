<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\AccountOwnershipTransfer;
use App\Models\Award;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\AwardResponseService;
use App\Services\AwardService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regression coverage for two bugs found during the Buyer dashboard audit:
 *
 * 1. PurchaseOrderService::complete() required status=delivered, a status
 *    Phase 1 never actually transitions a PO into from the Buyer/Award-accept
 *    path — meaning no PO created from an accepted Award could ever be
 *    completed. Fixed to accept from `issued` (Phase 1's real path) while
 *    still allowing `delivered` for any legacy-status record.
 *
 * 2. AccountOwnershipTransfer had no accept/reject endpoint for the proposed
 *    new owner, so a transfer could be initiated and cancelled but never
 *    actually completed.
 */
class BuyerPurchaseOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveAccount(string $capability, string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability' => $capability,
            'name' => ucfirst($capability).' Test User',
            'email' => $email,
            'phone' => '+1555'.random_int(1000000, 9999999),
            'password' => 'Password123!',
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

    private function makeOpenRfq(User $buyer): Rfq
    {
        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title' => 'Science lab kits',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items' => [
                ['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20],
            ],
        ]);

        return app(RfqService::class)->publish($rfq);
    }

    private function makeSubmittedQuotation(Rfq $rfq, User $supplier, float $total = 2000): Quotation
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QT-TEST-'.uniqid(),
            'rfq_id' => $rfq->id,
            'supplier_account_id' => $supplier->account->id,
            'submitted_by_user_id' => $supplier->id,
            'rfq_version_no' => $rfq->current_version_no,
            'subtotal' => $total,
            'grand_total' => $total,
            'currency_code' => 'USD',
            'lead_time_days' => 14,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'rfq_item_id' => $rfq->items->first()->id,
            'item_name' => 'Lab kit',
            'quantity' => 20,
            'unit_price' => $total / 20,
            'line_total' => $total,
        ]);

        $rfq->increment('quotations_count');

        return $quotation->fresh(['items']);
    }

    private function makeAcceptedAwardWithPo(User $buyer, User $supplier): array
    {
        $rfq = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);
        $award = app(AwardService::class)->create($quotation, $buyer);
        $award = app(AwardResponseService::class)->accept($award);

        return [$award, PurchaseOrder::where('award_id', $award->id)->firstOrFail()];
    }

    /* ── Purchase Order Phase 1 lifecycle ──────────────────────────────────── */

    public function test_purchase_order_is_created_issued_and_can_be_completed_directly_from_issued(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'pobuyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'posupplier1@example.com');

        [$award, $po] = $this->makeAcceptedAwardWithPo($buyer, $supplier);

        $this->assertSame('issued', $po->status);
        $this->assertSame(1, PurchaseOrder::where('award_id', $award->id)->count());

        $this->actingAs($buyer)
            ->post(route('buyer.purchase-orders.complete', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertSame('completed', $po->status);
        $this->assertNotNull($po->completed_at);
        $this->assertDatabaseHas('purchase_order_status_history', [
            'purchase_order_id' => $po->id,
            'old_status' => 'issued',
            'new_status' => 'completed',
        ]);
    }

    public function test_purchase_order_cannot_be_completed_twice(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'pobuyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'posupplier2@example.com');

        [, $po] = $this->makeAcceptedAwardWithPo($buyer, $supplier);

        $this->actingAs($buyer)->post(route('buyer.purchase-orders.complete', $po))->assertRedirect();
        $this->actingAs($buyer)->post(route('buyer.purchase-orders.complete', $po))
            ->assertForbidden();

        $this->assertSame(1, \App\Models\PurchaseOrderStatusHistory::where('purchase_order_id', $po->id)->count());
    }

    public function test_buyer_cannot_complete_another_buyers_purchase_order(): void
    {
        $buyerA = $this->makeActiveAccount('buyer', 'pobuyera@example.com');
        $buyerB = $this->makeActiveAccount('buyer', 'pobuyerb@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'posupplier3@example.com');

        [, $po] = $this->makeAcceptedAwardWithPo($buyerA, $supplier);

        $this->actingAs($buyerB)->post(route('buyer.purchase-orders.complete', $po))->assertForbidden();
        $this->assertSame('issued', $po->fresh()->status);
    }

    public function test_duplicate_award_accept_does_not_create_a_second_purchase_order(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'pobuyer3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'posupplier4@example.com');

        [$award] = $this->makeAcceptedAwardWithPo($buyer, $supplier);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AwardResponseService::class)->accept($award);
    }

    public function test_award_rejection_allows_re_award_and_preserves_the_previous_award(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'rebuyer1@example.com');
        $supplierA = $this->makeActiveAccount('supplier', 'resupplierA@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'resupplierB@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotationA = $this->makeSubmittedQuotation($rfq, $supplierA, 2000);
        $quotationB = $this->makeSubmittedQuotation($rfq, $supplierB, 2200);

        $awardA = app(AwardService::class)->create($quotationA, $buyer);
        app(AwardResponseService::class)->reject($awardA, 'Cannot meet the delivery timeline.');

        $this->assertSame('rejected_by_supplier', $awardA->fresh()->status);
        $this->assertDatabaseHas('awards', ['id' => $awardA->id, 'status' => 'rejected_by_supplier']);

        $awardB = app(AwardService::class)->create($quotationB, $buyer);

        $this->assertSame(1, $awardA->award_attempt_no);
        $this->assertSame(2, $awardB->award_attempt_no);
        $this->assertSame('pending_supplier_response', $awardB->status);

        // Previous award is untouched history, not deleted.
        $this->assertDatabaseHas('awards', ['id' => $awardA->id]);
        $this->assertSame(2, Award::where('rfq_id', $rfq->id)->count());
    }

    public function test_second_award_cannot_be_created_while_one_is_pending_supplier_response(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'dupbuyer1@example.com');
        $supplierA = $this->makeActiveAccount('supplier', 'dupsupplierA@example.com');
        $supplierB = $this->makeActiveAccount('supplier', 'dupsupplierB@example.com');

        $rfq = $this->makeOpenRfq($buyer);
        $quotationA = $this->makeSubmittedQuotation($rfq, $supplierA, 2000);
        $quotationB = $this->makeSubmittedQuotation($rfq, $supplierB, 2200);

        app(AwardService::class)->create($quotationA, $buyer);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AwardService::class)->create($quotationB, $buyer);
    }

    /* ── Ownership transfer acceptance ─────────────────────────────────────── */

    private function makeOrganizationWithTwoMembers(string $ownerEmail, string $memberEmail): array
    {
        $owner = $this->makeActiveAccount('buyer', $ownerEmail);
        $owner->account->update(['account_type' => 'organization']);

        $memberUser = User::create([
            'name' => 'Member User',
            'email' => $memberEmail,
            'phone' => '+1555'.random_int(1000000, 9999999),
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $owner->account->id,
            'user_id' => $memberUser->id,
            'member_type' => 'member',
            'is_primary_owner' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return [$owner, $memberUser];
    }

    public function test_target_member_can_accept_an_ownership_transfer_and_becomes_primary_owner(): void
    {
        [$owner, $member] = $this->makeOrganizationWithTwoMembers('ownerX@example.com', 'memberX@example.com');

        $this->actingAs($owner)->post(route('buyer.ownership.transfer'), [
            'to_user_id' => $member->id,
            'reason' => 'Stepping back from procurement duties.',
        ])->assertRedirect();

        $transfer = AccountOwnershipTransfer::where('account_id', $owner->account->id)->firstOrFail();
        $this->assertSame('pending', $transfer->status);

        $this->actingAs($member)
            ->post(route('buyer.ownership.accept', $transfer))
            ->assertRedirect();

        $transfer->refresh();
        $this->assertSame('completed', $transfer->status);
        $this->assertNotNull($transfer->accepted_at);
        $this->assertNotNull($transfer->completed_at);

        $account = Account::find($owner->account->id);
        $this->assertSame($member->id, $account->primary_owner_user_id);

        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->id, 'user_id' => $member->id, 'is_primary_owner' => true,
        ]);
        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->id, 'user_id' => $owner->id, 'is_primary_owner' => false,
        ]);
    }

    public function test_target_member_can_reject_an_ownership_transfer(): void
    {
        [$owner, $member] = $this->makeOrganizationWithTwoMembers('ownerY@example.com', 'memberY@example.com');

        $this->actingAs($owner)->post(route('buyer.ownership.transfer'), ['to_user_id' => $member->id]);
        $transfer = AccountOwnershipTransfer::where('account_id', $owner->account->id)->firstOrFail();

        $this->actingAs($member)->post(route('buyer.ownership.reject', $transfer))->assertRedirect();

        $this->assertSame('rejected', $transfer->fresh()->status);
        $this->assertSame($owner->id, Account::find($owner->account->id)->primary_owner_user_id);
    }

    public function test_only_the_proposed_new_owner_can_accept_the_transfer(): void
    {
        [$owner, $member] = $this->makeOrganizationWithTwoMembers('ownerZ@example.com', 'memberZ@example.com');
        $outsider = $this->makeActiveAccount('buyer', 'outsiderZ@example.com');

        $this->actingAs($owner)->post(route('buyer.ownership.transfer'), ['to_user_id' => $member->id]);
        $transfer = AccountOwnershipTransfer::where('account_id', $owner->account->id)->firstOrFail();

        $this->actingAs($outsider)->post(route('buyer.ownership.accept', $transfer))->assertForbidden();
        $this->assertSame('pending', $transfer->fresh()->status);
    }
}
