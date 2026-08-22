<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BuyerRfqTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveBuyer(string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'buyer',
            'name'         => 'Test Buyer',
            'email'        => $email,
            'phone'        => '+15550001111',
            'password'     => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->buyerCapability()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    public function test_active_buyer_can_view_dashboard_rfq_index_and_create_page(): void
    {
        $user = $this->makeActiveBuyer('buyer1@example.com');

        $this->actingAs($user)->get(route('buyer.dashboard'))->assertOk()->assertSee('Open RFQs');
        $this->actingAs($user)->get(route('buyer.rfqs.index'))->assertOk();
        $this->actingAs($user)->get(route('buyer.rfqs.create'))->assertOk();
    }

    public function test_buyer_can_create_publish_and_view_an_rfq(): void
    {
        $user    = $this->makeActiveBuyer('buyer2@example.com');
        $account = $user->account;

        $rfq = app(RfqService::class)->saveDraft($account, $user, [
            'title'              => 'Lab microscopes',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'items'              => [
                ['item_type' => 'product', 'item_name' => 'Microscope', 'quantity' => 10],
            ],
        ]);

        $this->assertSame('draft', $rfq->status);
        $this->assertSame(1, $rfq->items()->count());
        $this->assertSame('RFQ-' . date('Y') . '-000001', $rfq->rfq_number);

        app(RfqService::class)->publish($rfq);
        $rfq->refresh();

        $this->assertSame('open', $rfq->status);
        $this->assertNotNull($rfq->published_at);

        $this->actingAs($user)->get(route('buyer.rfqs.show', $rfq))
            ->assertOk()
            ->assertSee('Lab microscopes')
            ->assertSee('Open');
    }

    public function test_buyer_without_active_capability_is_blocked_from_rfq_routes(): void
    {
        $user = $this->makeActiveBuyer('buyer3@example.com');
        $user->account->buyerCapability()->update(['status' => 'pending']);

        $this->actingAs($user)->get(route('buyer.rfqs.index'))->assertForbidden();

        // But the dashboard landing page still resolves — status banner, not a 403.
        $this->actingAs($user)->get(route('buyer.dashboard'))->assertOk()->assertSee('Under Review');
    }

    public function test_publish_requires_at_least_one_item(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user    = $this->makeActiveBuyer('buyer4@example.com');
        $account = $user->account;

        $rfq = app(RfqService::class)->saveDraft($account, $user, [
            'title'              => 'Empty RFQ',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'items'              => [
                ['item_type' => 'product', 'item_name' => 'Placeholder', 'quantity' => 1],
            ],
        ]);

        // Manually strip items to simulate a corrupted/edge state, then publish should reject it.
        $rfq->items()->delete();
        $rfq->update(['items_count' => 0]);

        app(RfqService::class)->publish($rfq);
    }
}
