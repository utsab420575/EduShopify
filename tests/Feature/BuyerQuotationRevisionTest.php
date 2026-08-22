<?php

namespace Tests\Feature;

use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerQuotationRevisionTest extends TestCase
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

    public function test_submitting_and_revising_a_quotation_writes_immutable_revision_snapshots(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier1@example.com');

        $rfq = $this->makeOpenRfq($buyer);

        $quotation = app(QuotationService::class)->submit($rfq, $supplier->account, $supplier, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 50],
            ],
        ]);

        $this->assertSame(1, $quotation->current_revision_no);
        $this->assertDatabaseHas('quotation_revisions', [
            'quotation_id' => $quotation->id,
            'revision_no' => 1,
            'grand_total' => 1000,
        ]);

        $revised = app(QuotationService::class)->revise($quotation, [
            'items' => [
                ['rfq_item_id' => $rfq->items->first()->id, 'item_name' => 'Lab kit', 'quantity' => 20, 'unit_price' => 45],
            ],
        ], $supplier);

        $this->assertSame(2, $revised->current_revision_no);
        $this->assertDatabaseHas('quotation_revisions', [
            'quotation_id' => $quotation->id,
            'revision_no' => 2,
            'grand_total' => 900,
        ]);

        // Revision 1's snapshot must remain untouched (immutable).
        $this->assertDatabaseHas('quotation_revisions', [
            'quotation_id' => $quotation->id,
            'revision_no' => 1,
            'grand_total' => 1000,
        ]);

        $this->actingAs($buyer)->get(route('buyer.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Revision History')
            ->assertSee('Revision 1')
            ->assertSee('Revision 2');
    }
}
