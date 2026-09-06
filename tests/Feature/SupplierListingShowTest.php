<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingTierPrice;
use App\Models\ListingType;
use App\Models\ListingVariant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The supplier listing detail page (show.blade.php) was restructured from
 * one long scroll into a tabbed layout: Overview / Specifications /
 * Variants, with quantity-break tier pricing split so global (base-product)
 * tiers live in Overview and per-variant tiers live inline with each
 * variant in the Variants tab (native <details>, not Alpine, so the same
 * markup keeps working when injected into the Step 4 wizard's preview
 * modal via x-html). The sidebar "Listing Information" card is now
 * type-aware ("Product Information" / "Service Information"), and a "View
 * as Buyer" button links to the real public storefront page — gated so it
 * only links when the listing is actually publicly visible.
 */
class SupplierListingShowTest extends TestCase
{
    use RefreshDatabase;

    private function makeSupplier(string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ListingTypeSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'supplier',
            'name'         => 'Listing Show Test Supplier',
            'email'        => $email,
            'phone'        => '+15550007777',
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

        $plan = SubscriptionPlan::create([
            'name' => 'Free Plan', 'slug' => 'free-plan-listing-show-' . uniqid(), 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);
        $subscription = Subscription::create([
            'supplier_account_id' => $account->id, 'plan_id' => $plan->id, 'selected_by_user_id' => $user->id,
            'provider' => 'free', 'status' => 'pending',
        ]);
        $subscription->activate();

        return $user->fresh();
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Electronics', 'slug' => 'electronics-listing-show-' . uniqid(), 'type' => 'product', 'approval_status' => 'approved', 'is_active' => true]);
    }

    public function test_a_product_listing_with_variants_and_tiers_renders_correctly_in_the_new_tabbed_layout(): void
    {
        $supplier = $this->makeSupplier('listing-show-product@example.com');
        $productType = ListingType::where('code', 'product')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Test Widget',
            'slug' => 'test-widget-listing-show-' . uniqid(),
            'main_category_id' => $this->category()->id,
            'base_price' => 10,
            'currency_code' => 'USD',
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        ListingTierPrice::create(['listing_id' => $listing->id, 'listing_variant_id' => null, 'min_quantity' => 10, 'max_quantity' => 49, 'unit_price' => 9, 'currency_code' => 'USD']);

        $variant = ListingVariant::create(['listing_id' => $listing->id, 'name' => 'Red / Large', 'sku' => 'TW-RL', 'price' => 12, 'currency_code' => 'USD', 'stock_quantity' => 5, 'is_active' => true]);
        ListingTierPrice::create(['listing_id' => $listing->id, 'listing_variant_id' => $variant->id, 'min_quantity' => 5, 'max_quantity' => null, 'unit_price' => 11, 'currency_code' => 'USD']);

        $attribute = \App\Models\Attribute::create(['name' => 'Material', 'slug' => 'material-listing-show-' . uniqid(), 'input_type' => 'text', 'is_active' => true]);
        \App\Models\ListingAttributeValue::create(['listing_id' => $listing->id, 'attribute_id' => $attribute->id, 'value_text' => 'Aluminum']);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.show', $listing));

        $response->assertOk();
        $html = $response->getContent();

        // Tabs present, Variants tab included since this is a product.
        $this->assertStringContainsString("tab = 'overview'", $html);
        $this->assertStringContainsString("tab = 'specifications'", $html);
        $this->assertStringContainsString("tab = 'variants'", $html);

        // Specifications grid is single-column now, and the value actually renders.
        $this->assertStringContainsString('Material', $html);
        $this->assertStringContainsString('Aluminum', $html);
        $this->assertStringNotContainsString('grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5', $html);

        // Global tier pricing lives in Overview (with the base product), not a separate flat section.
        $this->assertSame(1, substr_count($html, 'Quantity Break / Tier Pricing'));

        // Per-variant tier pricing is an inline, JS-free <details> disclosure, not a separate section.
        $this->assertStringContainsString('quantity-break tier', $html);
        $this->assertStringContainsString('<details', $html);

        // Sidebar's Quick Info card badges the listing type, type-aware.
        $this->assertMatchesRegularExpression('/>\s*Product\s*</', $html);
        $this->assertStringNotContainsString('Listing Information', $html);

        // Draft listing — "View as Buyer" must be disabled, not linked.
        $this->assertStringContainsString('disabled title="Preview available once this listing is approved and published"', $html);
        $this->assertStringNotContainsString(route('frontend.listings.show', $listing), $html);
    }

    public function test_a_service_listing_has_no_variants_tab_and_a_service_labeled_sidebar(): void
    {
        $supplier = $this->makeSupplier('listing-show-service@example.com');
        $serviceType = ListingType::where('code', 'service')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $serviceType->id,
            'listing_type' => 'service',
            'name' => 'Test Consulting',
            'slug' => 'test-consulting-listing-show-' . uniqid(),
            'main_category_id' => $this->category()->id,
            'base_price' => 100,
            'currency_code' => 'USD',
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.show', $listing));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString("tab = 'variants'", $html);
        $this->assertMatchesRegularExpression('/>\s*Service\s*</', $html);
        $this->assertDoesNotMatchRegularExpression('/>\s*Product\s*</', $html);
    }

    public function test_a_published_listing_shows_a_working_view_as_buyer_link(): void
    {
        $supplier = $this->makeSupplier('listing-show-published@example.com');
        $productType = ListingType::where('code', 'product')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Published Widget',
            'slug' => 'published-widget-listing-show-' . uniqid(),
            'main_category_id' => $this->category()->id,
            'base_price' => 10,
            'currency_code' => 'USD',
            'setup_step' => 4,
            'approval_status' => 'approved',
            'is_active' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.show', $listing));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('href="' . route('frontend.listings.show', $listing) . '"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_a_listing_belonging_to_another_supplier_is_forbidden(): void
    {
        $owner = $this->makeSupplier('listing-show-owner@example.com');
        $other = $this->makeSupplier('listing-show-other@example.com');
        $productType = ListingType::where('code', 'product')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $owner->account->id,
            'created_by_user_id' => $owner->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Owner Widget',
            'slug' => 'owner-widget-listing-show-' . uniqid(),
            'main_category_id' => $this->category()->id,
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        $this->actingAs($other)->get(route('supplier.catalog.listings.show', $listing))->assertForbidden();
    }

    public function test_the_preview_fragment_used_by_the_step4_wizard_modal_stays_flat_without_the_tab_shell(): void
    {
        $supplier = $this->makeSupplier('listing-show-fragment@example.com');
        $productType = ListingType::where('code', 'product')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Fragment Widget',
            'slug' => 'fragment-widget-listing-show-' . uniqid(),
            'main_category_id' => $this->category()->id,
            'base_price' => 10,
            'currency_code' => 'USD',
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($supplier)->getJson(route('supplier.catalog.listings.preview', $listing));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $fragmentHtml = $response->json('html');

        $this->assertIsString($fragmentHtml);
        $this->assertStringNotContainsString("x-data=\"{ tab:", $fragmentHtml,
            'The x-html-injected wizard preview fragment must never gain the Alpine tab shell — it would be permanently inert.');
        // Sidebar content still renders in the fragment.
        $this->assertStringContainsString('Quick Info', $fragmentHtml);
    }
}
