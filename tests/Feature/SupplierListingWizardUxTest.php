<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * A batch of Step 2/3/4 UX fixes on the supplier listing wizard
 * (resources/views/backend/supplier/catalog/listings/wizard.blade.php):
 *
 * 1. MOQ field now has an explanatory tooltip.
 * 2. "Enable Volume Pricing" toggle replaced with a "Do you offer bulk
 *    discounts?" Yes/No question (same underlying has_tier_pricing field).
 * 3. Root cause of the Step 4 "variants not showing" report: the Variant
 *    Management section (and the Step 3 "Product Inventory" block, and the
 *    stepper's service-vs-product labels) compared the JS `listingType`
 *    state — which holds the numeric listing_type_id — against the literal
 *    string 'product'/'service'. Since listing_type_id is never that
 *    string, the comparison was always false and the section never
 *    rendered for ANY product listing. Fixed by comparing against the
 *    resolved DB id instead (same pattern already used correctly elsewhere
 *    in this same file for the "service" gate).
 * 4. Step 2's category tree now jumps to the page containing the already-
 *    selected category on load, instead of always starting at page 1.
 * 5. All four "Save Draft" buttons now swap icon/text and disable while a
 *    save request is in flight.
 * 6. A completion-percentage bar was added near the stepper.
 */
class SupplierListingWizardUxTest extends TestCase
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
            'name'         => 'Wizard UX Test Supplier',
            'email'        => $email,
            'phone'        => '+15550008888',
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
            'name' => 'Free Plan', 'slug' => 'free-plan-wizard-ux-' . uniqid(), 'billing_type' => 'free',
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

    private function makeCategories(int $count): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(fn ($i) => Category::create([
            'name' => "Category {$i}", 'slug' => "category-{$i}-wizard-ux-" . uniqid(),
            'type' => 'product', 'approval_status' => 'approved', 'is_active' => true,
        ]));
    }

    public function test_the_create_page_has_the_moq_tooltip_and_the_bulk_discount_question_not_the_old_toggle(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-create@example.com');
        $this->makeCategories(3);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.create'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('MOQ</strong> = minimum quantity a buyer must purchase.', $html);
        $this->assertStringContainsString('Example: 100 pieces', $html);
        $this->assertStringContainsString('Do you offer bulk discounts?', $html);
        $this->assertStringNotContainsString('Enable Volume Pricing', $html, 'The old toggle label must be fully replaced.');
    }

    public function test_the_create_page_resolves_real_product_and_service_type_ids_not_literal_strings(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-types@example.com');
        $this->makeCategories(3);

        $productId = ListingType::where('code', 'product')->value('id');
        $serviceId = ListingType::where('code', 'service')->value('id');

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.create'));
        $response->assertOk();
        $html = $response->getContent();

        // The x-data="{...}" attribute is double-quoted HTML, and json_encode()'s
        // own quotes are HTML-escaped by Blade's {{ }} to &quot; so they don't
        // break out of it — that's the actual on-the-wire format to check for.
        $this->assertStringContainsString('productTypeId: &quot;' . $productId . '&quot;,', $html);
        $this->assertStringContainsString('serviceTypeId: &quot;' . $serviceId . '&quot;,', $html);
        $this->assertStringNotContainsString("listingType === 'product'", $html, 'The always-false string comparison must be gone — it hid the Step 4 variant section for every product listing.');
        $this->assertStringNotContainsString("listingType === 'service'", $html);
        $this->assertStringContainsString('listingType == productTypeId', $html);
    }

    public function test_editing_a_product_listing_renders_the_variant_management_section_gated_correctly(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-variants@example.com');
        $categories = $this->makeCategories(3);
        $productType = ListingType::where('code', 'product')->firstOrFail();

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Test Widget',
            'slug' => 'test-widget-wizard-ux-' . uniqid(),
            'main_category_id' => $categories->first()->id,
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.edit', $listing));

        $response->assertOk();
        $html = $response->getContent();

        // The section exists in the markup and is gated on the real id, not
        // the broken literal-string comparison — confirming the fix applies
        // on the actual edit page, not just in isolation.
        $this->assertStringContainsString('Configure distinct SKU variations', $html);
        $this->assertStringContainsString('Auto-Generate Combinations', $html);
        // listing_type_id is stored/read as a PHP int (see Listing::setListingTypeIdAttribute),
        // so json_encode() emits a bare number here — unlike productTypeId/serviceTypeId,
        // which are explicitly cast to string in the Blade config for this exact reason.
        $this->assertStringContainsString('listingType: ' . $productType->id . ',', $html);
    }

    public function test_editing_a_listing_whose_category_is_past_page_one_still_ships_the_auto_jump_logic(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-category-page@example.com');
        $categories = $this->makeCategories(15); // catPerPage is 10, so category #12 sits on page 2
        $productType = ListingType::where('code', 'product')->firstOrFail();
        $targetCategory = $categories[11];

        $listing = Listing::create([
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $supplier->id,
            'listing_number' => 'LST-TEST-' . uniqid(),
            'listing_type_id' => $productType->id,
            'listing_type' => 'product',
            'name' => 'Test Widget Two',
            'slug' => 'test-widget-two-wizard-ux-' . uniqid(),
            'main_category_id' => $targetCategory->id,
            'setup_step' => 4,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.edit', $listing) . '?step=2');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('selectedCategoryId: ' . $targetCategory->id . ',', $html);
        $this->assertStringContainsString('categoryNodes.findIndex', $html, 'The auto-jump-to-page logic must be present so the selected category is not stuck on page 1.');
    }

    public function test_all_four_save_draft_buttons_show_a_saving_state(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-save-draft@example.com');
        $this->makeCategories(3);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.create'));
        $response->assertOk();
        $html = $response->getContent();

        // 4 Save Draft buttons (steps 1-4) plus the pre-existing "Save Variations"
        // button in Step 4, which already used this exact spinner expression.
        $this->assertSame(5, substr_count($html, "isSaving ? 'fa-circle-notch fa-spin' : 'fa-floppy-disk'"),
            'All four Save Draft buttons must swap to a spinner while saving.');
        $this->assertStringContainsString("isSaving ? 'Saving...' : 'Save Draft'", $html);
        $this->assertStringContainsString("isSaving ? 'Saving...' : 'Save as Draft'", $html);
    }

    public function test_the_wizard_shows_a_completion_percentage(): void
    {
        $supplier = $this->makeSupplier('wizard-ux-completion@example.com');
        $this->makeCategories(3);

        $response = $this->actingAs($supplier)->get(route('supplier.catalog.listings.create'));
        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('completionPercent', $html);
        $this->assertStringContainsString("completionPercent + '% Complete'", $html);
    }
}
