<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerRfqVersioningTest extends TestCase
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

    public function test_editing_an_open_rfq_records_a_version_and_notifies_engaged_suppliers(): void
    {
        $buyer    = $this->makeActiveAccount('buyer', 'buyer1@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier1@example.com');

        $rfq = $this->makeOpenRfq($buyer);

        Quotation::create([
            'quotation_number'     => 'QT-TEST-1',
            'rfq_id'               => $rfq->id,
            'supplier_account_id'  => $supplier->account->id,
            'submitted_by_user_id' => $supplier->id,
            'rfq_version_no'       => $rfq->current_version_no,
            'subtotal'             => 1000,
            'grand_total'          => 1000,
            'currency_code'        => 'USD',
            'status'               => 'submitted',
            'submitted_at'         => now(),
        ]);

        $this->assertSame(1, $rfq->current_version_no);

        $response = $this->actingAs($buyer)->put(route('buyer.rfqs.update', $rfq), [
            'title' => 'Science lab kits',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'items' => [
                ['item_type' => 'product', 'item_name' => 'Lab kit', 'quantity' => 20],
            ],
        ]);

        $response->assertRedirect(route('buyer.rfqs.show', $rfq));

        $rfq->refresh();
        $this->assertSame(2, $rfq->current_version_no);
        $this->assertDatabaseHas('rfq_change_logs', [
            'rfq_id' => $rfq->id,
            'from_version_no' => 1,
            'to_version_no' => 2,
            'change_level' => 'major',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $supplier->id,
        ]);

        $this->actingAs($buyer)->get(route('buyer.rfqs.show', $rfq))
            ->assertOk()
            ->assertSee('v1 &rarr; v2', false);
    }

    public function test_buyer_can_extend_the_quotation_deadline_of_an_open_rfq(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer2@example.com');
        $rfq   = $this->makeOpenRfq($buyer);

        $newDeadline = now()->addDays(15)->format('Y-m-d H:i:s');

        $this->actingAs($buyer)->post(route('buyer.rfqs.extend-deadline', $rfq), [
            'deadline_type' => 'quotation',
            'new_deadline' => $newDeadline,
            'reason' => 'Need more time to gather quotes.',
        ])->assertRedirect();

        $this->assertDatabaseHas('rfq_deadline_extensions', [
            'rfq_id' => $rfq->id,
            'deadline_type' => 'quotation',
        ]);

        $this->assertSame(
            \Illuminate\Support\Carbon::parse($newDeadline)->timestamp,
            $rfq->fresh()->quotation_deadline->timestamp
        );
    }

    public function test_a_draft_rfq_can_still_be_edited_freely_without_versioning(): void
    {
        $buyer = $this->makeActiveAccount('buyer', 'buyer3@example.com');

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'              => 'Draft RFQ',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items'              => [
                ['item_type' => 'product', 'item_name' => 'Item', 'quantity' => 1],
            ],
        ]);

        $this->actingAs($buyer)->put(route('buyer.rfqs.update', $rfq), [
            'title' => 'Updated Draft RFQ',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'items' => [
                ['item_type' => 'product', 'item_name' => 'Item', 'quantity' => 1],
            ],
        ])->assertRedirect();

        $rfq->refresh();
        $this->assertSame('Updated Draft RFQ', $rfq->title);
        $this->assertSame(1, $rfq->current_version_no);
        $this->assertDatabaseMissing('rfq_change_logs', ['rfq_id' => $rfq->id]);
    }
}
