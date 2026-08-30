<?php

namespace Tests\Feature;

use App\Livewire\Auth\SupplierApplication;
use App\Models\Account;
use App\Models\CapabilityType;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use App\Models\Exhibition;
use App\Models\SocialPlatform;
use App\Models\State;
use App\Models\SubscriptionPlan;
use App\Models\SupplierType;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Drives the consolidated 7-step Supplier Application wizard end to end,
 * exactly as a fresh draft-capability applicant would: company info with a
 * repeatable location, branding uploads, types/exhibitions, contact/social,
 * required-document upload, business hours, and finally plan selection —
 * confirming the capability moves from draft to pending review afterward.
 */
class SupplierApplicationWizardTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeDraftSupplier(string $email): array
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'supplier',
            'name' => 'Wizard Test User', 'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999), 'password' => 'Password123!',
            'organization_display_name' => 'Wizard Test Co',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);

        return [$user->fresh(), $user->account->fresh()];
    }

    public function test_full_wizard_walkthrough_submits_application_on_free_plan(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftSupplier('wizard-free@example.com');

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $state2 = State::create(['country_id' => $country->id, 'name' => 'Abu Dhabi', 'is_active' => true]);
        $city = City::create(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'Dubai City', 'is_active' => true]);
        $city2 = City::create(['country_id' => $country->id, 'state_id' => $state2->id, 'name' => 'Abu Dhabi City', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer-w', 'code' => 'MFGW', 'is_active' => true]);
        $exhibition = Exhibition::create(['name' => 'EduExpo', 'slug' => 'eduexpo-w', 'is_active' => true]);

        $docType = DocumentType::create(['name' => 'Trade License', 'slug' => 'trade-license-w', 'code' => 'TLW', 'is_required' => true, 'is_active' => true]);
        DocumentTypeEnable::create([
            'document_type_id' => $docType->id,
            'capability_type_id' => CapabilityType::where('code', 'supplier')->value('id'),
            'is_required' => true,
        ]);

        $freePlan = SubscriptionPlan::create([
            'name' => 'Free Tier', 'slug' => 'free-tier-w', 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 5, 'max_monthly_quotations' => 10, 'rfq_delay_minutes' => 0,
        ]);

        $component = Livewire::actingAs($user)->test(SupplierApplication::class);

        // Step 1 — Company Information + Locations
        $component
            ->set('display_name', 'Wizard Test Co')
            ->set('legal_name', 'Wizard Test Co LLC')
            ->set('legal_entity_type', 'Limited Company')
            ->set('founded_year', 2015)
            ->set('employees', 25)
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.state_id', $state->id)
            ->set('locations.0.city_id', $city->id)
            ->set('locations.0.address', '1 Industrial Ave, Dubai')
            ->call('addLocation')
            ->set('locations.1.country_id', $country->id)
            ->set('locations.1.state_id', $state2->id)
            ->set('locations.1.city_id', $city2->id)
            ->set('locations.1.address', '2 Branch Street, Abu Dhabi')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2);

        $this->assertSame(2, $account->locations()->count());

        // Step 2 — Branding & Media
        $logo = UploadedFile::fake()->image('logo.png');
        $gallery = [UploadedFile::fake()->image('g1.png'), UploadedFile::fake()->image('g2.png')];

        $component
            ->set('logo', $logo)
            ->set('gallery_files', $gallery)
            ->set('video_urls', ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'])
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 3);

        $this->assertSame(2, $account->galleryImages()->count());
        $this->assertSame(1, $account->videos()->count());

        // Step 3 — Supplier Types & Exhibitions
        $component
            ->set('supplier_type_ids', [$supplierType->id])
            ->set('exhibition_ids', [$exhibition->id])
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $this->assertTrue($account->supplierTypes()->where('supplier_types.id', $supplierType->id)->exists());
        $this->assertTrue($account->exhibitions()->where('exhibitions.id', $exhibition->id)->exists());

        // Step 4 — Contact Information & Social Media
        $linkedinPlatformId = SocialPlatform::where('slug', 'linkedin')->value('id');

        $component
            ->set('contact_person', 'Jane Doe')
            ->set('contact_email', 'wizard-free@example.com')
            ->set('contact_phone', '+971500000000')
            ->set("social_links.$linkedinPlatformId.url", 'https://linkedin.com/company/wizardtestco')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 5);

        $this->assertTrue($account->supplierProfile->isComplete());
        $this->assertSame(
            'https://linkedin.com/company/wizardtestco',
            $account->socialLinks()->where('social_platform_id', $linkedinPlatformId)->value('url')
        );

        // Step 5 — Verification Documents: nextStep must block until required doc uploaded.
        $component->call('nextStep')->assertHasErrors('documents');
        $this->assertSame(5, $component->get('step'));

        $tradeLicenseFile = UploadedFile::fake()->create('trade.pdf', 200, 'application/pdf');
        $component
            ->set('new_document_type_id', (string) $docType->id)
            ->set('new_file', $tradeLicenseFile)
            ->call('addDocument')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('supplier_documents', [
            'supplier_account_id' => $account->id,
            'document_type_id' => $docType->id,
            'is_current' => true,
        ]);

        $component->call('nextStep')->assertHasNoErrors()->assertSet('step', 6);

        // Step 6 — Business Hours
        $component
            ->set('default_open_time', '08:00')
            ->set('default_close_time', '18:00')
            ->call('applyDefaultHours')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 7);

        $this->assertDatabaseHas('business_hours', [
            'supplier_account_id' => $account->id,
            'account_location_id' => null,
            'day_of_week' => 1,
            'open_time' => '08:00',
        ]);

        // Step 7 renders as soon as the wizard lands here, before any plan
        // is chosen — the preview modal is only conditionally rendered, so
        // this must not error trying to read a null selected plan.
        $component->assertOk();
        $component->assertDontSee('Preview Full Application');

        // Step 7 — Choose Your Plan (Free): selecting only marks the choice
        // and reveals the "Final Submit" / "Preview Full Application"
        // buttons; nothing is submitted until Final Submit is clicked.
        $component->call('selectPlan', $freePlan->id)->assertHasNoErrors();
        $this->assertSame('draft', $account->supplierCapability->fresh()->status);
        $component->assertSee('Preview Full Application');
        $component->assertSee('Final Submit');
        $component->assertSee('Back to Continue');

        $component->call('confirmFreePlanSubmission')->assertHasNoErrors();

        $this->assertSame('pending', $account->supplierCapability->fresh()->status);
        $this->assertDatabaseHas('subscriptions', [
            'supplier_account_id' => $account->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
        ]);
    }

    /**
     * social_links is polymorphic under this app's registered morph map
     * (Account rows are stored under the alias 'account', not the literal
     * class name — see AppServiceProvider::registerMorphMap()). Saving via
     * a raw socialable_type string would silently write the wrong value and
     * make every saved link invisible on the next page load; saving must go
     * through the Account::socialLinks() relation so it resolves correctly.
     */
    public function test_social_links_are_saved_and_resumed_correctly_under_the_morph_map(): void
    {
        $this->seedBase();
        [$user, $account] = $this->makeDraftSupplier('wizard-social@example.com');

        $linkedinId = SocialPlatform::where('slug', 'linkedin')->value('id');
        $youtubeId = SocialPlatform::where('slug', 'youtube')->value('id');

        $component = Livewire::actingAs($user)->test(SupplierApplication::class);
        $component
            ->set("social_links.$linkedinId.url", 'https://linkedin.com/company/wizard-social')
            ->set("social_links.$youtubeId.url", 'https://youtube.com/@wizard-social')
            ->call('saveDraft');

        $this->assertDatabaseHas('social_links', [
            'socialable_type' => 'account',
            'socialable_id' => $account->id,
            'social_platform_id' => $linkedinId,
            'url' => 'https://linkedin.com/company/wizard-social',
        ]);

        // A fresh mount (simulated re-login) must actually see the saved links.
        $resumed = Livewire::actingAs($user->fresh())->test(SupplierApplication::class);
        $resumed->assertSet("social_links.$linkedinId.url", 'https://linkedin.com/company/wizard-social');
        $resumed->assertSet("social_links.$youtubeId.url", 'https://youtube.com/@wizard-social');

        // Clearing a field removes the saved row rather than leaving a stale one.
        $resumed->set("social_links.$youtubeId.url", '')->call('saveDraft');
        $this->assertDatabaseMissing('social_links', ['socialable_id' => $account->id, 'social_platform_id' => $youtubeId]);
    }

    /**
     * A user who completes steps 1-3, navigates back to review step 2, then
     * logs out mid-application must resume on step 2 next login — not
     * restart at step 1 — while steps 1-3 stay unlocked on the step bar.
     * A brand-new Livewire::test() call stands in for "logs back in": it
     * mounts a fresh component instance with no in-memory state, exactly
     * like a new page load after a fresh login would.
     */
    public function test_resumes_at_the_exact_step_left_off_after_a_simulated_logout(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftSupplier('wizard-resume@example.com');

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $city = City::create(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'Dubai City', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer-r', 'code' => 'MFGR', 'is_active' => true]);

        $session1 = Livewire::actingAs($user)->test(SupplierApplication::class);

        $session1
            ->set('display_name', 'Wizard Resume Co')
            ->set('legal_name', 'Wizard Resume Co LLC')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.state_id', $state->id)
            ->set('locations.0.city_id', $city->id)
            ->set('locations.0.address', '1 Industrial Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        $session1
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 3);

        $session1
            ->set('supplier_type_ids', [$supplierType->id])
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 4);

        // Navigates back to review step 2, then (simulated) logs out right there.
        $session1->call('goToStep', 2)->assertSet('step', 2);

        $this->assertDatabaseHas('supplier_profiles', [
            'account_id' => $account->id,
            'current_step' => 2,
            'max_step_reached' => 4,
        ]);

        // Fresh "login" — a brand new component instance, no shared state.
        $session2 = Livewire::actingAs($user->fresh())->test(SupplierApplication::class);

        $session2->assertSet('step', 2);
        $session2->assertSet('maxStepReached', 4);
        $session2->assertSet('display_name', 'Wizard Resume Co');

        // Steps 1-4 are still reachable via the step bar; step 5 is not yet.
        $session2->call('goToStep', 4)->assertSet('step', 4);
        $session2->call('goToStep', 5)->assertSet('step', 4);
    }

    /**
     * Regression test: Step 2 (Branding & Media) previously had every field
     * marked nullable, so clicking Continue with nothing filled in silently
     * advanced to step 3 — no field on that step could ever block progress.
     * A logo is now required (unless one was already saved on a prior
     * visit), so this must be blocked.
     */
    public function test_cannot_advance_past_step_2_without_a_logo(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftSupplier('wizard-step2@example.com');

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $city = City::create(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'Dubai City', 'is_active' => true]);

        $component = Livewire::actingAs($user)->test(SupplierApplication::class);

        $component
            ->set('display_name', 'Step2 Test Co')
            ->set('legal_name', 'Step2 Test Co LLC')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.state_id', $state->id)
            ->set('locations.0.city_id', $city->id)
            ->set('locations.0.address', '1 Industrial Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        // No logo uploaded — clicking Continue must be blocked, not silently pass.
        $component->call('nextStep')->assertHasErrors('logo');
        $this->assertSame(2, $component->get('step'));

        // Uploading the logo unblocks it.
        $component
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 3);
    }

    /**
     * Step 2 previously had no way to remove a gallery image — neither a
     * pending upload before it's saved, nor one already saved from an
     * earlier visit. Both must now be removable.
     */
    public function test_gallery_images_can_be_removed_both_pending_and_already_saved(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftSupplier('wizard-gallery@example.com');

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $city = City::create(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'Dubai City', 'is_active' => true]);

        $component = Livewire::actingAs($user)->test(SupplierApplication::class);

        $component
            ->set('display_name', 'Gallery Test Co')
            ->set('legal_name', 'Gallery Test Co LLC')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.state_id', $state->id)
            ->set('locations.0.city_id', $city->id)
            ->set('locations.0.address', '1 Industrial Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        // Two pending (not-yet-saved) uploads — remove the first before saving.
        $component->set('gallery_files', [
            UploadedFile::fake()->image('g1.png'),
            UploadedFile::fake()->image('g2.png'),
        ]);
        $component->call('removeGalleryFile', 0);
        $this->assertCount(1, $component->get('gallery_files'));

        $component
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('nextStep')->assertHasNoErrors();

        $this->assertSame(1, $account->galleryImages()->count());

        // Now remove the already-saved one.
        $savedImage = $account->galleryImages()->first();
        $component->call('removeExistingGalleryImage', $savedImage->id)->assertHasNoErrors();

        $this->assertSame(0, $account->galleryImages()->count());
        Storage::disk('public')->assertMissing($savedImage->image_path);
    }

    /**
     * The payment-timing override (spec rule 28 relaxed): a draft-status
     * capability must now be allowed to reach Stripe checkout, not just an
     * already-approved one. Uses a paid plan with no stripe_price_id so the
     * request fails on the *next* guard (plan not yet purchasable) instead of
     * calling the real Stripe API — proving the capability guard itself was
     * passed.
     */
    public function test_checkout_no_longer_blocks_a_draft_capability_account(): void
    {
        $this->seedBase();
        [$user, $account] = $this->makeDraftSupplier('wizard-paid@example.com');

        $paidPlan = SubscriptionPlan::create([
            'name' => 'Growth Pro', 'slug' => 'growth-pro-w', 'billing_type' => 'monthly',
            'price' => 99, 'currency_code' => 'USD', 'is_free' => false, 'is_active' => true,
            'max_active_listings' => 50, 'max_monthly_quotations' => 100, 'rfq_delay_minutes' => 0,
            'stripe_price_id' => null,
        ]);

        $this->assertSame('draft', $account->capabilityStatus('supplier'));

        $response = $this->actingAs($user)->post(route('supplier.subscribe', $paidPlan->slug));

        $response->assertRedirect(route('supplier.pricing'));
        $this->assertSame('This plan is not yet available for purchase.', session('error'));
    }
}
