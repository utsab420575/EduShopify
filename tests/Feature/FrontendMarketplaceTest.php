<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\Listing;
use App\Models\Review;
use App\Models\Rfq;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FrontendMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeAccount(string $capability, string $email): \App\Models\User
    {
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

        return $user->fresh();
    }

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Laboratory Equipment',
            'slug' => 'laboratory-equipment-'.Str::random(6),
            'type' => 'product',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    private function makeSupplierProfile(Account $account, array $overrides = []): \App\Models\SupplierProfile
    {
        $attributes = array_merge([
            'display_name' => 'Acme Supply Co',
            'profile_completed_at' => now(),
            'rating' => 4.5,
            'reviews_count' => 3,
        ], $overrides);

        // AccountRegistrationService already creates a draft supplier_profiles
        // row for a Supplier-capability account, so update it rather than
        // inserting a second row (account_id is unique).
        $profile = $account->supplierProfile()->first();

        if ($profile) {
            $profile->update($attributes);

            return $profile->fresh();
        }

        return $account->supplierProfile()->create($attributes);
    }

    private function makeListing(Account $supplierAccount, Category $category, array $overrides = []): Listing
    {
        return Listing::create(array_merge([
            'supplier_account_id' => $supplierAccount->id,
            'created_by_user_id' => $supplierAccount->primary_owner_user_id,
            'listing_type' => 'product',
            'listing_number' => 'LST-'.Str::random(8),
            'main_category_id' => $category->id,
            'name' => 'Digital Microscope 1000X',
            'slug' => 'digital-microscope-1000x-'.Str::random(6),
            'pricing_type' => 'fixed',
            'base_price' => 199.99,
            'currency_code' => 'USD',
            'approval_status' => 'approved',
            'is_active' => true,
            'published_at' => now(),
        ], $overrides));
    }

    /* ── Homepage ───────────────────────────────────────────────────────── */

    public function test_guest_homepage_loads(): void
    {
        $this->seedBase();

        $this->get('/')->assertOk()->assertSee('EduShopify');
    }

    public function test_homepage_only_shows_public_featured_listing(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'homesupplier@example.com');
        $this->makeSupplierProfile($supplier->account);
        $category = $this->makeCategory();

        $visible = $this->makeListing($supplier->account, $category, ['name' => 'Visible Public Listing', 'is_featured' => true]);
        $hidden = $this->makeListing($supplier->account, $category, ['name' => 'Hidden Pending Listing', 'approval_status' => 'pending', 'published_at' => null, 'is_featured' => true]);

        $response = $this->get('/');

        $response->assertSee('Visible Public Listing');
        $response->assertDontSee('Hidden Pending Listing');
    }

    /* ── Listing eligibility (frontend_workflow.md Part 9) ────────────────── */

    public function test_approved_active_published_listing_is_publicly_visible(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier1@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $this->get('/listing/'.$listing->slug)->assertOk()->assertSee($listing->name);
    }

    public function test_pending_listing_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier2@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), ['approval_status' => 'pending', 'published_at' => null]);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_rejected_listing_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier3@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), ['approval_status' => 'rejected', 'published_at' => null]);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_unpublished_listing_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier4@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), ['published_at' => null]);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_inactive_listing_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier5@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), ['is_active' => false]);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_listing_from_suspended_supplier_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier6@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $supplier->account->update(['status' => 'suspended']);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_listing_with_inactive_supplier_capability_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier7@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $supplier->account->supplierCapability()->update(['status' => 'suspended']);

        $this->get('/listing/'.$listing->slug)->assertNotFound();
    }

    public function test_deleted_listing_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'listingsupplier8@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());
        $slug = $listing->slug;
        $listing->delete();

        $this->get('/listing/'.$slug)->assertNotFound();
    }

    /* ── Catalog ────────────────────────────────────────────────────────── */

    public function test_catalog_filters_by_category_and_preserves_query_on_pagination_links(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'catalogsupplier@example.com');
        $this->makeSupplierProfile($supplier->account);
        $categoryA = $this->makeCategory();
        $categoryB = $this->makeCategory();

        $inCategory = $this->makeListing($supplier->account, $categoryA, ['name' => 'Category A Listing']);
        $outOfCategory = $this->makeListing($supplier->account, $categoryB, ['name' => 'Category B Listing']);

        $response = $this->get('/catalog?category='.$categoryA->slug);

        $response->assertSee('Category A Listing');
        $response->assertDontSee('Category B Listing');
    }

    public function test_products_page_only_shows_products_and_services_page_only_shows_services(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'typesupplier@example.com');
        $this->makeSupplierProfile($supplier->account);
        $category = $this->makeCategory();

        $product = $this->makeListing($supplier->account, $category, ['name' => 'A Real Product']);
        $service = $this->makeListing($supplier->account, $category, [
            'name' => 'A Real Service',
            'listing_type' => 'service',
            'slug' => 'a-real-service-'.Str::random(6),
            'pricing_type' => 'quote_only',
            'base_price' => null,
        ]);

        $this->get('/products')->assertSee('A Real Product')->assertDontSee('A Real Service');
        $this->get('/services')->assertSee('A Real Service')->assertDontSee('A Real Product');
    }

    /* ── Supplier eligibility (frontend_workflow.md Part 10) ──────────────── */

    public function test_eligible_supplier_storefront_is_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'storefront1@example.com');
        $profile = $this->makeSupplierProfile($supplier->account, ['display_name' => 'Visible Storefront Co']);

        $this->get('/supplier/'.$profile->slug)->assertOk()->assertSee('Visible Storefront Co');
    }

    public function test_suspended_supplier_storefront_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'storefront2@example.com');
        $profile = $this->makeSupplierProfile($supplier->account);
        $supplier->account->update(['status' => 'suspended']);

        $this->get('/supplier/'.$profile->slug)->assertNotFound();
    }

    public function test_supplier_with_pending_capability_is_not_public(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'storefront3@example.com');
        $profile = $this->makeSupplierProfile($supplier->account);
        $supplier->account->supplierCapability()->update(['status' => 'pending']);

        $this->get('/supplier/'.$profile->slug)->assertNotFound();
    }

    public function test_only_published_reviews_appear_on_supplier_storefront(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'reviewsupplier@example.com');
        $profile = $this->makeSupplierProfile($supplier->account);
        $buyer = $this->makeAccount('buyer', 'reviewbuyer@example.com');

        Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $buyer->id,
            'review_context' => 'quotation_experience',
            'rating' => 5,
            'title' => 'Published Review Title',
            'comment' => 'Great supplier to work with.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Review::create([
            'buyer_account_id' => $buyer->account->id,
            'supplier_account_id' => $supplier->account->id,
            'created_by_user_id' => $buyer->id,
            'review_context' => 'quotation_experience',
            'rating' => 1,
            'title' => 'Hidden Review Title',
            'comment' => 'This should never be public.',
            'status' => 'pending',
        ]);

        $response = $this->get('/supplier/'.$profile->slug);

        $response->assertSee('Published Review Title');
        $response->assertDontSee('Hidden Review Title');
    }

    public function test_supplier_storefront_never_exposes_document_file_paths(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'docsupplier@example.com');
        $profile = $this->makeSupplierProfile($supplier->account);

        \App\Models\SupplierDocument::create([
            'supplier_account_id' => $supplier->account->id,
            'document_type_id' => null,
            'uploaded_by_user_id' => $supplier->id,
            'custom_name' => 'Trade License',
            'file_path' => 'private/secret-trade-license-12345.pdf',
            'status' => 'verified',
            'is_current' => true,
        ]);

        $this->get('/supplier/'.$profile->slug)
            ->assertOk()
            ->assertDontSee('secret-trade-license-12345.pdf');
    }

    /* ── Public RFQ eligibility (frontend_workflow.md Part 13) ────────────── */

    public function test_global_open_published_rfq_is_public(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'rfqbuyer1@example.com');

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-TEST-0001',
            'buyer_account_id' => $buyer->account->id,
            'created_by_user_id' => $buyer->id,
            'visibility_type' => 'global',
            'title' => 'Public Global RFQ Title',
            'currency_code' => 'USD',
            'quotation_deadline' => now()->addDays(10),
            'status' => 'open',
            'published_at' => now(),
        ]);

        $this->get('/opportunities/'.$rfq->rfq_number)->assertOk()->assertSee('Public Global RFQ Title');
        $this->get('/opportunities')->assertSee('Public Global RFQ Title');
    }

    public function test_selected_supplier_rfq_is_never_public(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'rfqbuyer2@example.com');

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-TEST-0002',
            'buyer_account_id' => $buyer->account->id,
            'created_by_user_id' => $buyer->id,
            'visibility_type' => 'selected_suppliers',
            'title' => 'Private Selected Supplier RFQ',
            'currency_code' => 'USD',
            'quotation_deadline' => now()->addDays(10),
            'status' => 'open',
            'published_at' => now(),
        ]);

        $this->get('/opportunities/'.$rfq->rfq_number)->assertNotFound();
        $this->get('/opportunities')->assertDontSee('Private Selected Supplier RFQ');
    }

    public function test_expired_rfq_is_not_public(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'rfqbuyer3@example.com');

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-TEST-0003',
            'buyer_account_id' => $buyer->account->id,
            'created_by_user_id' => $buyer->id,
            'visibility_type' => 'global',
            'title' => 'Expired RFQ Title',
            'currency_code' => 'USD',
            'quotation_deadline' => now()->subDays(2),
            'status' => 'open',
            'published_at' => now()->subDays(20),
        ]);

        $this->get('/opportunities/'.$rfq->rfq_number)->assertNotFound();
    }

    public function test_draft_unpublished_rfq_is_not_public(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'rfqbuyer4@example.com');

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-TEST-0004',
            'buyer_account_id' => $buyer->account->id,
            'created_by_user_id' => $buyer->id,
            'visibility_type' => 'global',
            'title' => 'Draft RFQ Title',
            'currency_code' => 'USD',
            'quotation_deadline' => now()->addDays(10),
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->get('/opportunities/'.$rfq->rfq_number)->assertNotFound();
    }

    /* ── Guest inquiry (frontend_workflow.md Part 54) ──────────────────────── */

    public function test_guest_can_submit_a_valid_listing_inquiry(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'inquirysupplier1@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $response = $this->post('/inquire/'.$listing->slug, [
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
            'message' => 'Please send more details about pricing.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_inquiries', [
            'email' => 'jane@example.com',
            'listing_id' => $listing->id,
            'supplier_account_id' => $supplier->account->id,
            'status' => 'new',
        ]);
    }

    public function test_inquiry_with_invalid_email_is_rejected(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'inquirysupplier2@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $response = $this->post('/inquire/'.$listing->slug, [
            'name' => 'Jane Buyer',
            'email' => 'not-an-email',
            'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_inquiry_against_a_private_listing_is_rejected(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'inquirysupplier3@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), ['approval_status' => 'pending', 'published_at' => null]);

        $this->post('/inquire/'.$listing->slug, [
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
            'message' => 'Hello',
        ])->assertNotFound();

        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_honeypot_field_silently_blocks_bot_submissions(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'inquirysupplier4@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory());

        $this->post('/inquire/'.$listing->slug, [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'spam',
            'website' => 'https://spam.example.com',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactInquiry::count());
    }

    /* ── Auth handoff (frontend_workflow.md Parts 50-53) ───────────────────── */

    public function test_guest_post_rfq_handoff_redirects_to_login(): void
    {
        $this->seedBase();

        $this->get('/handoff/post-rfq')->assertRedirect(route('login'));
    }

    public function test_authenticated_buyer_post_rfq_handoff_goes_straight_to_rfq_create(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'handoffbuyer@example.com');

        $this->actingAs($buyer)
            ->get('/handoff/post-rfq')
            ->assertRedirect(route('buyer.rfqs.create'));
    }

    public function test_authenticated_supplier_submit_quotation_handoff_resolves_to_the_real_opportunity(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'handoffrfqbuyer@example.com');
        $supplier = $this->makeAccount('supplier', 'handoffsupplier@example.com');

        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-TEST-0005',
            'buyer_account_id' => $buyer->account->id,
            'created_by_user_id' => $buyer->id,
            'visibility_type' => 'global',
            'title' => 'Handoff Target RFQ',
            'currency_code' => 'USD',
            'quotation_deadline' => now()->addDays(10),
            'status' => 'open',
            'published_at' => now(),
        ]);

        $this->actingAs($supplier)
            ->get('/handoff/submit-quotation/'.$rfq->rfq_number)
            ->assertRedirect(route('supplier.opportunities.show', $rfq));
    }

    public function test_login_resolves_a_pending_frontend_intent_instead_of_the_default_destination(): void
    {
        $this->seedBase();
        $buyer = $this->makeAccount('buyer', 'intentbuyer@example.com');

        $this->withSession(['frontend_intent' => ['action' => 'post_rfq', 'params' => []]])
            ->post('/login', [
                'email' => 'intentbuyer@example.com',
                'password' => 'Password123!',
            ])
            ->assertRedirect(route('buyer.rfqs.create'));
    }

    /* ── Security ──────────────────────────────────────────────────────────── */

    public function test_listing_name_is_escaped_on_the_public_page(): void
    {
        $this->seedBase();
        $supplier = $this->makeAccount('supplier', 'xsssupplier@example.com');
        $this->makeSupplierProfile($supplier->account);
        $listing = $this->makeListing($supplier->account, $this->makeCategory(), [
            'name' => '<script>alert(1)</script> Malicious Listing',
        ]);

        $this->get('/listing/'.$listing->slug)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('alert(1)');
    }

    /* ── Regression ────────────────────────────────────────────────────────── */

    public function test_registration_and_login_pages_still_load(): void
    {
        $this->get('/register')->assertOk();
        $this->get('/login')->assertOk();
    }
}
