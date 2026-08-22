<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerExtendedFeaturesTest extends TestCase
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

        return $quotation->fresh(['items']);
    }

    public function test_buyer_can_browse_and_view_a_supplier_profile(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier1@example.com');
        $supplier->account->supplierProfile()->update(['display_name' => 'Acme Labs Co']);

        $this->actingAs($buyer)->get(route('buyer.suppliers.index'))
            ->assertOk()
            ->assertSee('Acme Labs Co');

        $this->actingAs($buyer)->get(route('buyer.suppliers.show', $supplier->account))
            ->assertOk()
            ->assertSee('Acme Labs Co');
    }

    public function test_buyer_can_save_and_unsave_a_supplier(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier2@example.com');

        $this->actingAs($buyer)->post(route('buyer.suppliers.save', $supplier->account))
            ->assertRedirect();

        $this->assertDatabaseHas('saved_items', [
            'account_id' => $buyer->account->id,
            'item_type'  => 'supplier',
            'item_id'    => $supplier->account->id,
        ]);

        $this->actingAs($buyer)->get(route('buyer.saved-items.index', ['type' => 'supplier']))->assertOk();

        $this->actingAs($buyer)->post(route('buyer.suppliers.save', $supplier->account))
            ->assertRedirect();

        $this->assertDatabaseMissing('saved_items', [
            'account_id' => $buyer->account->id,
            'item_type'  => 'supplier',
            'item_id'    => $supplier->account->id,
        ]);
    }

    public function test_buyer_can_message_a_supplier_and_the_supplier_side_conversation_exists(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer3@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier3@example.com');

        $this->actingAs($buyer)->post(route('buyer.suppliers.message', $supplier->account))
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', ['context_type' => 'general']);
        $conversation = \App\Models\Conversation::first();

        $this->assertDatabaseHas('conversation_accounts', ['conversation_id' => $conversation->id, 'account_id' => $buyer->account->id]);
        $this->assertDatabaseHas('conversation_accounts', ['conversation_id' => $conversation->id, 'account_id' => $supplier->account->id]);

        $this->actingAs($buyer)->post(route('buyer.messages.store', $conversation), [
            'body' => 'Hello, interested in your lab kits.',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id'   => $conversation->id,
            'sender_account_id' => $buyer->account->id,
            'body'              => 'Hello, interested in your lab kits.',
        ]);

        $this->actingAs($buyer)->get(route('buyer.messages.index'))->assertOk();
        $this->actingAs($buyer)->get(route('buyer.messages.show', $conversation))->assertOk()->assertSee('Hello, interested');

        // A different buyer must not see this conversation.
        $otherBuyer = $this->makeActiveAccount('buyer', 'otherbuyer@example.com');
        $this->actingAs($otherBuyer)->get(route('buyer.messages.show', $conversation))->assertForbidden();
    }

    public function test_buyer_can_review_a_quotation_and_a_completed_purchase_order(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer4@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier4@example.com');

        $rfq       = $this->makeOpenRfq($buyer);
        $quotation = $this->makeSubmittedQuotation($rfq, $supplier);

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), [
            'rating' => 4,
            'comment' => 'Good communication.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'buyer_account_id'    => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'quotation_id'        => $quotation->id,
            'review_context'      => 'quotation_experience',
            'status'              => 'pending',
        ]);

        // Reviewing twice is rejected.
        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-quotation', $quotation), [
            'rating' => 5,
        ]);

        $this->assertSame(1, \App\Models\Review::where('quotation_id', $quotation->id)->count());

        // Completed purchase order review.
        $award = \App\Models\Award::create([
            'award_number'        => 'AWD-TEST-1',
            'rfq_id'              => $rfq->id,
            'quotation_id'        => $quotation->id,
            'buyer_account_id'    => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'awarded_by_user_id'  => $buyer->id,
            'award_attempt_no'    => 1,
            'status'              => 'accepted',
            'response_deadline'   => now()->addDays(3),
            'awarded_at'          => now(),
            'accepted_at'         => now(),
        ]);

        $po = PurchaseOrder::create([
            'po_number'           => 'PO-TEST-1',
            'award_id'            => $award->id,
            'rfq_id'              => $rfq->id,
            'quotation_id'        => $quotation->id,
            'buyer_account_id'    => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id'  => $buyer->id,
            'subtotal'            => 5000,
            'grand_total'         => 5000,
            'currency_code'       => 'USD',
            'status'              => 'completed',
            'issued_at'           => now(),
            'completed_at'        => now(),
        ]);

        $this->actingAs($buyer)->post(route('buyer.reviews.store-for-purchase-order', $po), [
            'rating' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'purchase_order_id' => $po->id,
            'review_context'    => 'purchase_experience',
        ]);
    }

    public function test_buyer_can_create_and_reply_to_a_support_ticket(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer5@example.com');

        $this->actingAs($buyer)->post(route('buyer.tickets.store'), [
            'subject' => 'Question about an RFQ',
            'description' => 'How do I extend my quotation deadline?',
            'priority' => 'normal',
        ])->assertRedirect();

        $ticket = \App\Models\Ticket::firstOrFail();
        $this->assertSame($buyer->account->id, $ticket->account_id);

        $this->actingAs($buyer)->get(route('buyer.tickets.index'))->assertOk()->assertSee('Question about an RFQ');
        $this->actingAs($buyer)->get(route('buyer.tickets.show', $ticket))->assertOk();

        $this->actingAs($buyer)->post(route('buyer.tickets.reply', $ticket), [
            'message' => 'Following up on this.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id, 'message' => 'Following up on this.']);

        // Another buyer cannot view this ticket.
        $otherBuyer = $this->makeActiveAccount('buyer', 'otherbuyer2@example.com');
        $this->actingAs($otherBuyer)->get(route('buyer.tickets.show', $ticket))->assertForbidden();
    }

    public function test_buyer_can_edit_their_profile(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer6@example.com');
        $this->seed(\Database\Seeders\GeographySeeder::class);

        $this->actingAs($buyer)->get(route('buyer.profile.edit'))->assertOk();

        $this->actingAs($buyer)->put(route('buyer.profile.update'), [
            'display_name' => 'Updated Buyer Name',
            'contact_person' => 'Jane Doe',
            'email' => 'jane@example.com',
            'country_id' => \App\Models\Country::first()->id,
            'address' => '123 Main St',
        ])->assertRedirect();

        $this->assertSame('Updated Buyer Name', $buyer->account->buyerProfile->fresh()->display_name);
    }

    public function test_rfq_create_prefills_supplier_from_query_param(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer7@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier7@example.com');

        $this->actingAs($buyer)->get(route('buyer.rfqs.create', ['supplier' => $supplier->account->id]))
            ->assertOk()
            ->assertSee($supplier->account->supplierProfile->display_name);
    }
}
