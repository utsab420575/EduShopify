<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategorySuggestion;
use App\Models\Listing;
use App\Models\ProductDetail;
use App\Models\Review;
use App\Models\Rfq;
use App\Models\RoleRequest;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AccountConversionService;
use App\Services\AccountModerationService;
use App\Services\AccountRegistrationService;
use App\Services\AttributeSuggestionService;
use App\Services\BrandModerationService;
use App\Services\CategorySuggestionService;
use App\Services\ListingModerationService;
use App\Services\ReviewModerationService;
use App\Services\RfqService;
use App\Services\RoleRequestService;
use App\Services\TicketAdminService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeAdmin(string $email = 'admin@example.com'): User
    {
        $this->seedBase();

        $systemAccount = \App\Models\Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name'              => 'Test Admin',
            'email'             => $email,
            'phone'             => '+10000000001',
            'password'          => bcrypt('Password123!'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);

        \App\Models\AccountMember::create([
            'account_id'       => $systemAccount->id,
            'user_id'          => $admin->id,
            'member_type'      => 'owner',
            'is_primary_owner' => true,
            'status'           => 'active',
            'joined_at'        => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    private function makeActiveAccount(string $capability, string $email): User
    {
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

    public function test_non_admin_cannot_reach_the_admin_panel(): void
    {
        $this->seedBase();
        $buyer = $this->makeActiveAccount('buyer', 'buyer1@example.com');

        $this->actingAs($buyer)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_reach_the_dashboard_and_new_resources(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/catalog/listings')->assertOk();
        $this->actingAs($admin)->get('/admin/reviews')->assertOk();
        $this->actingAs($admin)->get('/admin/tickets')->assertOk();
        $this->actingAs($admin)->get('/admin/accounts')->assertOk();
        $this->actingAs($admin)->get('/admin/catalog/categories')->assertOk();
        $this->actingAs($admin)->get('/admin/catalog/brands')->assertOk();
        $this->actingAs($admin)->get('/admin/catalog/attributes')->assertOk();
        $this->actingAs($admin)->get('/admin/procurement/rfqs')->assertOk();
        $this->actingAs($admin)->get('/admin/system/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/system/audit')->assertOk();
    }

    public function test_listing_approval_flow(): void
    {
        $admin    = $this->makeAdmin('admin2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier1@example.com');

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id'  => $supplier->id,
            'listing_type'        => 'product',
            'listing_number'      => 'LST-TEST-1',
            'name'                => 'Test Microscope',
            'slug'                => 'test-microscope',
            'pricing_type'        => 'quote_only',
            'sales_mode'          => 'rfq_only',
            'approval_status'     => 'pending',
            'is_active'           => true,
        ]);

        app(ListingModerationService::class)->approve($listing, $admin);

        $listing->refresh();
        $this->assertSame('approved', $listing->approval_status);
        $this->assertNotNull($listing->published_at);
        $this->assertTrue($listing->isPublished());

        $this->assertDatabaseHas('activity_log', ['description' => 'Listing approved']);
    }

    public function test_review_publish_recalculates_supplier_rating(): void
    {
        $admin    = $this->makeAdmin('admin3@example.com');
        $buyer    = $this->makeActiveAccount('buyer', 'buyer2@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier2@example.com');

        $review = Review::create([
            'buyer_account_id'    => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id'  => $buyer->id,
            'review_context'      => 'quotation_experience',
            'rating'              => 4,
            'status'              => 'pending',
        ]);

        app(ReviewModerationService::class)->publish($review, $admin);

        $this->assertSame('published', $review->fresh()->status);
        $supplier->account->supplierProfile->refresh();
        $this->assertEquals(4.0, (float) $supplier->account->supplierProfile->rating);
        $this->assertSame(1, $supplier->account->supplierProfile->reviews_count);
    }

    public function test_admin_can_reply_to_a_ticket_and_it_reaches_the_requester(): void
    {
        $admin  = $this->makeAdmin('admin4@example.com');
        $buyer  = $this->makeActiveAccount('buyer', 'buyer3@example.com');

        $ticket = app(TicketService::class)->create($buyer->account, $buyer, [
            'subject'     => 'Need help',
            'description' => 'How do I extend a deadline?',
        ]);

        app(TicketAdminService::class)->reply($ticket, $admin, 'You can extend it from the RFQ page.');

        $ticket->refresh();
        $this->assertSame('answered', $ticket->status);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id'         => $ticket->id,
            'sender_account_id' => null,
            'message'           => 'You can extend it from the RFQ page.',
        ]);

        $this->actingAs($buyer)->get(route('buyer.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('You can extend it from the RFQ page.');
    }

    public function test_account_suspend_and_reactivate(): void
    {
        $admin  = $this->makeAdmin('admin5@example.com');
        $buyer  = $this->makeActiveAccount('buyer', 'buyer4@example.com');

        $service = app(AccountModerationService::class);
        $service->suspend($buyer->account, $admin, 'Suspicious activity');

        $this->assertSame('suspended', $buyer->account->fresh()->status);

        // A suspended buyer is now blocked from their own dashboard.
        $this->actingAs($buyer)->get(route('buyer.rfqs.index'))->assertForbidden();

        $service->reactivate($buyer->account->fresh());
        $this->assertSame('active', $buyer->account->fresh()->status);
    }

    public function test_account_conversion_approval_resets_active_supplier_capability(): void
    {
        $admin = $this->makeAdmin('admin6@example.com');
        $user  = $this->makeActiveAccount('supplier', 'supplier3@example.com');

        $request = \App\Models\AccountConversionRequest::create([
            'account_id'                 => $user->account->id,
            'from_type'                  => 'individual',
            'to_type'                    => 'organization',
            'proposed_display_name'      => 'Acme Supplies Ltd',
            'proposed_organization_data' => ['legal_name' => 'Acme Supplies Ltd'],
            'status'                     => 'pending',
            'submitted_by_user_id'       => $user->id,
            'submitted_at'               => now(),
        ]);

        app(AccountConversionService::class)->approve($request, $admin);

        $account = $user->account->fresh();
        $this->assertSame('organization', $account->account_type);
        $this->assertSame('Acme Supplies Ltd', $account->display_name);
        $this->assertSame('pending', $account->supplierCapability->fresh()->status);
        $this->assertDatabaseHas('account_type_changes', ['account_id' => $account->id]);
    }

    public function test_category_suggestion_approval_creates_a_category(): void
    {
        $admin    = $this->makeAdmin('admin7@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier4@example.com');

        $suggestion = CategorySuggestion::create([
            'supplier_account_id'  => $supplier->account->id,
            'suggested_by_user_id' => $supplier->id,
            'proposed_name'        => 'Laboratory Robotics',
            'proposed_type'        => 'product',
            'status'               => 'pending',
        ]);

        app(CategorySuggestionService::class)->approve($suggestion, $admin);

        $suggestion->refresh();
        $this->assertSame('approved', $suggestion->status);
        $this->assertNotNull($suggestion->resulting_category_id);
        $this->assertDatabaseHas('categories', ['name' => 'Laboratory Robotics', 'approval_status' => 'approved']);
    }

    public function test_brand_approval(): void
    {
        $admin    = $this->makeAdmin('admin8@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier5@example.com');

        $brand = Brand::create([
            'owner_type'          => 'supplier',
            'supplier_account_id' => $supplier->account->id,
            'name'                => 'Acme Optics',
            'slug'                => 'acme-optics',
            'approval_status'     => 'pending',
            'is_active'           => true,
        ]);

        app(BrandModerationService::class)->approve($brand, $admin);

        $this->assertSame('approved', $brand->fresh()->approval_status);
    }

    public function test_attribute_suggestion_approval_creates_an_attribute_with_values(): void
    {
        $admin    = $this->makeAdmin('admin9@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier6@example.com');

        $suggestion = \App\Models\AttributeSuggestion::create([
            'supplier_account_id'  => $supplier->account->id,
            'suggested_by_user_id' => $supplier->id,
            'proposed_name'        => 'Lens Coating',
            'proposed_input_type'  => 'select',
            'proposed_values'      => ['Anti-glare', 'UV Protected'],
            'status'               => 'pending',
        ]);

        app(AttributeSuggestionService::class)->approve($suggestion, $admin);

        $suggestion->refresh();
        $this->assertSame('approved', $suggestion->status);
        $attribute = Attribute::find($suggestion->resulting_attribute_id);
        $this->assertNotNull($attribute);
        $this->assertSame(2, $attribute->values()->count());
    }

    public function test_role_request_approval_creates_an_account_scoped_role(): void
    {
        $admin = $this->makeAdmin('admin10@example.com');
        $buyer = $this->makeActiveAccount('buyer', 'buyer5@example.com');

        $request = RoleRequest::create([
            'account_id'            => $buyer->account->id,
            'requested_by_user_id'  => $buyer->id,
            'role_name'             => 'junior_buyer',
            'display_name'          => 'Junior Buyer',
            'capability_scope'      => 'buyer',
            'requested_permissions' => ['rfq.view', 'rfq.create'],
            'description'           => 'Limited RFQ access for a junior staff member.',
            'status'                => 'pending',
        ]);

        app(RoleRequestService::class)->approve($request, $admin);

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertDatabaseHas('roles', ['account_id' => $buyer->account->id, 'name' => 'junior_buyer']);

        $role = \App\Models\Role::where('account_id', $buyer->account->id)->where('name', 'junior_buyer')->firstOrFail();
        $this->assertEqualsCanonicalizing(['rfq.view', 'rfq.create'], $role->permissions->pluck('name')->all());
    }

    public function test_rfq_admin_approval_gate_when_setting_enabled(): void
    {
        $this->seedBase();
        Setting::set('rfq', 'rfq_requires_admin_approval', true);

        $admin    = $this->makeAdmin('admin11@example.com');
        $buyer    = $this->makeActiveAccount('buyer', 'buyer6@example.com');
        $supplier = $this->makeActiveAccount('supplier', 'supplier7@example.com');

        \App\Models\Subscription::create([
            'supplier_account_id' => $supplier->account->id,
            'plan_id'             => \App\Models\SubscriptionPlan::create([
                'name' => 'Free', 'slug' => 'free-' . uniqid(), 'billing_type' => 'free',
                'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            ])->id,
            'selected_by_user_id' => $supplier->id,
            'provider'            => 'free',
            'status'              => 'active',
        ]);

        $rfq = app(RfqService::class)->saveDraft($buyer->account, $buyer, [
            'title'              => 'Needs admin approval',
            'visibility_type'    => 'global',
            'quotation_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'items'              => [
                ['item_type' => 'product', 'item_name' => 'Item', 'quantity' => 1],
            ],
        ]);

        app(RfqService::class)->publish($rfq);
        $rfq->refresh();

        $this->assertSame('pending_approval', $rfq->status);
        $this->assertDatabaseMissing('rfq_supplier_queue', ['rfq_id' => $rfq->id]);

        // Buyer can no longer see a "publish" button on an RFQ awaiting admin review.
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($buyer)->allows('publish', $rfq));

        app(RfqService::class)->approve($rfq, $admin);
        $rfq->refresh();

        $this->assertSame('open', $rfq->status);
        $this->assertNotNull($rfq->approved_by_user_id);
        $this->assertDatabaseHas('rfq_supplier_queue', ['rfq_id' => $rfq->id]);
    }

    public function test_setting_get_and_set_round_trip_with_cache(): void
    {
        $this->assertSame(72, Setting::get('award', 'award_response_hours', 72));

        Setting::set('award', 'award_response_hours', 48);

        $this->assertSame(48, Setting::get('award', 'award_response_hours', 72));
    }
}
