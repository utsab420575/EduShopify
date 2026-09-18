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

    public function test_buyer_can_view_add_requirement_page(): void
    {
        $user = $this->makeActiveBuyer('buyer_req1@example.com');

        $this->actingAs($user)
            ->get(route('buyer.rfqs.add-requirement'))
            ->assertOk()
            ->assertSee('Add Requirement')
            ->assertSee('Quotation Only')
            ->assertSee('Detailed Scope of Work');
    }

    public function test_buyer_can_store_requirement_with_attachment_and_specifications(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = $this->makeActiveBuyer('buyer_req2@example.com');
        $account = $user->account;

        $file = \Illuminate\Http\UploadedFile::fake()->create('specification_doc.pdf', 150, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('buyer.rfqs.store-requirement'), [
                'item_name' => 'Custom CNC Precision Milling',
                'item_type' => 'service',
                'description' => 'Tolerances within +/- 0.05mm. Anodized black finish.',
                'quantity' => 25,
                'custom_unit' => 'Parts',
                'estimated_unit_price' => 75.50,
                'spec_keys' => ['Material', 'Tolerance'],
                'spec_values' => ['Aluminum 6061-T6', '+/- 0.05mm'],
                'attachments' => [$file],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertStringContainsString('restore_items=1', $response->headers->get('Location'));
        $this->assertStringContainsString('new_requirement_id=', $response->headers->get('Location'));

        $this->assertStringContainsString('rfq_id=', $response->headers->get('Location'));

        $item = \App\Models\RfqItem::latest()->first();
        $this->assertNotNull($item);
        $this->assertSame('Custom CNC Precision Milling', $item->item_name);
        $this->assertSame('service', $item->item_type);
        $this->assertTrue($item->isRequirement());
        $this->assertFalse($item->isMarketplaceProduct());
        $this->assertSame(25.0, (float) $item->quantity);
        $this->assertSame(75.50, (float) $item->estimated_unit_price);
        $this->assertSame('Aluminum 6061-T6', collect($item->specs)->firstWhere('name', 'Material')['value'] ?? null);
        $this->assertSame('+/- 0.05mm', collect($item->specs)->firstWhere('name', 'Tolerance')['value'] ?? null);
        $this->assertCount(1, $item->getMedia('attachments'));
        $this->assertSame('specification_doc', $item->getMedia('attachments')->first()->name);
        $this->assertStringEndsWith('.pdf', $item->getMedia('attachments')->first()->file_name);
    }

    public function test_buyer_can_add_requirement_to_existing_draft_rfq_preserving_marketplace_item(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = $this->makeActiveBuyer('buyer_req_existing@example.com');
        $account = $user->account;

        // Draft RFQ created with an initial marketplace product item
        $rfq = app(RfqService::class)->saveDraft($account, $user, [
            'title'              => 'Lab Procurement RFQ',
            'visibility_type'    => 'global',
            'current_step'       => 1,
            'items'              => [
                [
                    'item_type' => 'product',
                    'item_name' => 'Microscope Objective Lens',
                    'quantity'  => 4,
                ],
            ],
        ]);

        $this->assertCount(1, $rfq->items);

        $response = $this->actingAs($user)
            ->post(route('buyer.rfqs.store-requirement'), [
                'rfq_id'      => $rfq->id,
                'item_name'   => 'Custom Lens Calibration Mount',
                'item_type'   => 'product',
                'description' => 'Precision 3D printed or milled mounting adapter.',
                'quantity'    => 2,
                'return_url'  => route('buyer.rfqs.edit', ['rfq' => $rfq->id, 'step' => 1]),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertStringContainsString('restore_items=1', $response->headers->get('Location'));
        $this->assertStringContainsString('rfq_id=' . $rfq->id, $response->headers->get('Location'));

        $rfq->refresh();
        $this->assertCount(2, $rfq->items);
        $this->assertSame('Microscope Objective Lens', $rfq->items->first()->item_name);
        $this->assertSame('Custom Lens Calibration Mount', $rfq->items->last()->item_name);
        $this->assertTrue($rfq->items->last()->isRequirement());
    }

    public function test_buyer_can_fetch_requirement_item_data(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = $this->makeActiveBuyer('buyer_req3@example.com');
        $account = $user->account;

        $rfq = app(RfqService::class)->saveDraft($account, $user, [
            'title' => 'Test RFQ for Requirement Data',
            'visibility_type' => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items' => [
                ['item_type' => 'product', 'item_name' => 'Existing Item', 'quantity' => 1],
            ],
        ]);

        $item = $rfq->items()->create([
            'item_type' => 'product',
            'item_name' => 'Custom Industrial Valve',
            'quantity' => 15,
            'custom_unit' => 'Sets',
            'estimated_unit_price' => 120.00,
            'specs' => [
                '__is_requirement' => 1,
                'description' => 'Heavy duty 316 stainless steel ball valve.',
                'Pressure Rating' => '1000 PSI',
            ],
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('buyer.rfqs.requirements.data', $item));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame('Custom Industrial Valve', $data['item_name']);
        $this->assertSame(15.0, (float) $data['quantity']);
        $this->assertSame('Sets', $data['custom_unit']);
        $this->assertSame(120.0, (float) $data['estimated_unit_price']);
        $this->assertTrue($data['is_requirement']);
        $this->assertSame('Heavy duty 316 stainless steel ball valve.', $data['specs']['description']);
        $this->assertSame('1000 PSI', $data['specs']['Pressure Rating']);
    }

    private function makeActiveSupplier(string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'supplier',
            'name'         => 'Test Supplier',
            'email'        => $email,
            'phone'        => '+15550002222',
            'password'     => 'Password123!',
        ]);

        $account = $user->account;
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $account->update(['status' => 'active']);
        $account->supplierCapability()->update(['status' => 'active']);

        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    private function giveActiveSubscription(User $supplier): \App\Models\Subscription
    {
        $plan = \App\Models\SubscriptionPlan::firstOrCreate([
            'slug' => 'free-plan-test',
        ], [
            'name'         => 'Free Plan',
            'billing_type' => 'free',
            'price'        => 0,
            'currency_code' => 'USD',
            'is_free'      => true,
            'is_active'    => true,
            'max_active_listings'    => 10,
            'max_monthly_quotations' => 50,
            'rfq_delay_minutes'      => 0,
        ]);

        $subscription = \App\Models\Subscription::create([
            'supplier_account_id' => $supplier->account->id,
            'plan_id'              => $plan->id,
            'selected_by_user_id'  => $supplier->id,
            'provider'             => 'free',
            'status'               => 'pending',
        ]);

        $subscription->activate();

        return $subscription->fresh();
    }

    public function test_supplier_sees_requirement_badges_and_reference_files_in_opportunity(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $buyer = $this->makeActiveBuyer('buyer_opp@example.com');
        $supplier = $this->makeActiveSupplier('supplier_opp@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'              => 'School Furniture Fabrication',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'items'              => [
                [
                    'item_type'            => 'product',
                    'item_name'            => 'Custom Ergonomic Desks',
                    'quantity'             => 40,
                    'specs'                => [
                        ['name' => '__is_requirement', 'value' => '1'],
                        ['name' => 'description', 'value' => 'Height-adjustable solid oak study desks.'],
                        ['name' => 'Warranty', 'value' => '5 Years'],
                    ],
                ],
            ],
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('desk_cad_drawing.pdf', 200, 'application/pdf');
        $item = $rfq->items()->first();
        $item->addMedia($file)->toMediaCollection('attachments');

        app(RfqService::class)->publish($rfq);

        $this->actingAs($supplier)
            ->get(route('supplier.opportunities.show', $rfq))
            ->assertOk()
            ->assertSee('Custom Ergonomic Desks')
            ->assertSee('Requirement (Quotation Only)')
            ->assertSee('Height-adjustable solid oak study desks.')
            ->assertSee("Buyer's Reference Files &amp; Drawings", false)
            ->assertSee('desk_cad_drawing.pdf');
    }

    public function test_supplier_and_buyer_quotation_views_display_requirement_details(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $buyer = $this->makeActiveBuyer('buyer_qview@example.com');
        $supplier = $this->makeActiveSupplier('supplier_qview@example.com');
        $this->giveActiveSubscription($supplier);

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'              => 'School Equipment Requisition',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'items'              => [
                [
                    'item_type'            => 'product',
                    'item_name'            => 'Science Lab Ventilation System',
                    'quantity'             => 5,
                    'specs'                => [
                        ['name' => '__is_requirement', 'value' => '1'],
                        ['name' => 'description', 'value' => 'High airflow stainless steel ducted ventilation.'],
                    ],
                ],
            ],
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('ventilation_blueprint.pdf', 300, 'application/pdf');
        $rfqItem = $rfq->items()->first();
        $rfqItem->addMedia($file)->toMediaCollection('attachments');

        app(RfqService::class)->publish($rfq);

        $quotation = \App\Models\Quotation::create([
            'quotation_number'     => 'QT-REQ-' . uniqid(),
            'rfq_id'               => $rfq->id,
            'supplier_account_id'  => $supplier->account->id,
            'submitted_by_user_id' => $supplier->id,
            'rfq_version_no'       => $rfq->current_version_no,
            'subtotal'             => 7500,
            'grand_total'          => 7500,
            'currency_code'        => 'USD',
            'status'               => 'submitted',
            'submitted_at'         => now(),
        ]);

        $qItem = \App\Models\QuotationItem::create([
            'quotation_id' => $quotation->id,
            'rfq_item_id'  => $rfqItem->id,
            'item_name'    => 'Ventilation System Proposal',
            'quantity'     => 5,
            'unit_price'   => 1500,
            'line_total'   => 7500,
        ]);

        // 1. Supplier quotation show page
        $this->actingAs($supplier)
            ->get(route('supplier.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Science Lab Ventilation System')
            ->assertSee('Requirement')
            ->assertSee('ventilation_blueprint.pdf');

        // 2. Buyer quotation show page
        $this->actingAs($buyer)
            ->get(route('buyer.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Ventilation System Proposal')
            ->assertSee('Requirement');
    }
}

