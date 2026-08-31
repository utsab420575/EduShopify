<?php

namespace Tests\Feature;

use App\Livewire\Auth\SupplierApplication;
use App\Livewire\Buyer\BuyerProfileOnboarding;
use App\Models\CapabilityType;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\SubscriptionPlan;
use App\Models\SupplierType;
use App\Services\AccountRegistrationService;
use App\Services\BuyerOnboardingStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A "both" (buy & sell) registration must finish Supplier onboarding first —
 * it's the longer, document + plan driven wizard — and only then hand off to
 * the short Buyer wizard, pre-filled from the just-completed Supplier
 * profile so nothing has to be retyped. Locations and social links need no
 * explicit sync: both are stored per-Account (App\Models\AccountLocation /
 * SocialLink), not per-capability, so they're already shared.
 */
class DualCapabilityOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeDraftBoth(string $email): array
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'both',
            'name' => 'Dual Wizard User', 'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999), 'password' => 'Password123!',
            'organization_display_name' => 'Dual Wizard Co',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);

        return [$user->fresh(), $user->account->fresh()];
    }

    public function test_a_both_registration_finishes_supplier_first_then_buyer_is_pre_filled(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftBoth('dual-both@example.com');

        // Registration must create both draft capabilities and both profile skeletons.
        $this->assertSame('draft', $account->capabilityStatus('buyer'));
        $this->assertSame('draft', $account->capabilityStatus('supplier'));
        $this->assertNotNull($account->buyerProfile);
        $this->assertNotNull($account->supplierProfile);

        // Both capabilities still draft — the state resolver must send this
        // account to Supplier first, not Buyer (the previous buyer-always-
        // wins behavior).
        $this->assertSame(
            route('supplier.onboarding.profile'),
            app(BuyerOnboardingStateService::class)->resolve($user)
        );

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $city = City::create(['country_id' => $country->id, 'state_id' => $state->id, 'name' => 'Dubai City', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer-dual', 'code' => 'MFGD', 'is_active' => true]);

        $freePlan = SubscriptionPlan::create([
            'name' => 'Free Tier', 'slug' => 'free-tier-dual', 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 5, 'max_monthly_quotations' => 10, 'rfq_delay_minutes' => 0,
        ]);

        $supplier = Livewire::actingAs($user)->test(SupplierApplication::class);

        // Step 1 — Company Information + Location
        $supplier
            ->set('display_name', 'Dual Wizard Co')
            ->set('legal_name', 'Dual Wizard Co LLC')
            ->set('website', 'https://dualwizard.example.com')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.state_id', $state->id)
            ->set('locations.0.city_id', $city->id)
            ->set('locations.0.address', '1 Industrial Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        // Step 2 — Branding & Media
        $supplier
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->set('profile_photo', UploadedFile::fake()->image('rep-photo.png'))
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 3);

        // Step 3 — Supplier Types
        $supplier
            ->set('supplier_type_ids', [$supplierType->id])
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 4);

        // Step 4 — Contact Information
        $supplier
            ->set('contact_person', 'Jane Doe')
            ->set('contact_email', 'dual-both@example.com')
            ->set('contact_phone', '+971500000000')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 5);

        // Step 5 — no required documents configured for supplier in this
        // test, so it advances straight through.
        $supplier->call('nextStep')->assertHasNoErrors()->assertSet('step', 6);

        // Step 6 — Business Hours
        $supplier->call('nextStep')->assertHasNoErrors()->assertSet('step', 7);

        // Step 7 — Plan + Final Submit
        $supplier->call('selectPlan', $freePlan->id)->assertHasNoErrors();
        $supplier->call('confirmFreePlanSubmission')->assertHasNoErrors();

        $this->assertSame('pending', $account->supplierCapability->fresh()->status);
        // Buyer must still be draft — auto-activation only happens once the
        // buyer wizard is actually submitted.
        $this->assertSame('draft', $account->buyerCapability->fresh()->status);

        // Supplier's Final Submit must redirect straight into the (now
        // pre-filled) Buyer wizard, not the supplier "pending review" page.
        $supplier->assertRedirect(route('buyer.onboarding.profile'));

        // The shared identity/contact fields must have been copied onto the
        // still-draft BuyerProfile.
        $buyerProfile = $account->buyerProfile->fresh();
        $this->assertSame('Dual Wizard Co', $buyerProfile->display_name);
        $this->assertSame('Jane Doe', $buyerProfile->contact_person);
        $this->assertSame('dual-both@example.com', $buyerProfile->email);
        $this->assertSame('+971500000000', $buyerProfile->phone);
        $this->assertSame('https://dualwizard.example.com', $buyerProfile->website);
        $this->assertSame($country->id, $buyerProfile->country_id);
        $this->assertSame($city->id, $buyerProfile->city_id);
        $this->assertSame('1 Industrial Ave, Dubai', $buyerProfile->address);
        $this->assertNotNull($buyerProfile->logo);
        $this->assertNotNull($buyerProfile->profile_photo);

        // The state resolver must now send the account to Buyer instead of
        // Supplier, since Supplier is no longer draft.
        $this->assertSame(
            route('buyer.onboarding.profile'),
            app(BuyerOnboardingStateService::class)->resolve($user)
        );

        // Mounting the Buyer wizard fresh must show the pre-filled fields —
        // and the location the Supplier wizard already saved, proving the
        // shared AccountLocation read needs no extra sync code.
        $buyer = Livewire::actingAs($user->fresh())->test(BuyerProfileOnboarding::class);
        $buyer->assertSet('display_name', 'Dual Wizard Co');
        $buyer->assertSet('contact_person', 'Jane Doe');
        $buyer->assertSet('email', 'dual-both@example.com');
        $buyer->assertSet('phone', '+971500000000');
        $this->assertSame(1, $account->locations()->count());
        $this->assertSame($country->id, $buyer->get('locations')[0]['country_id']);
        $this->assertSame('1 Industrial Ave, Dubai', $buyer->get('locations')[0]['address']);

        // The user only really needs to add Buyer Type and a profile photo
        // (mandatory, distinct concept from the supplier's already-copied
        // rep photo isn't force-reused for this — buyer supplies its own).
        $buyerType = \App\Models\BuyerType::first() ?? \App\Models\BuyerType::create(['name' => 'School', 'slug' => 'school-dual', 'is_active' => true]);

        $buyer
            ->set('buyer_type_ids', [$buyerType->id])
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        $buyer
            ->set('profile_photo', UploadedFile::fake()->image('buyer-photo.png'))
            ->call('confirmFinalSubmit')
            ->assertHasNoErrors();

        $this->assertSame('active', $account->buyerCapability->fresh()->status);
        $buyer->assertRedirect(route('buyer.dashboard'));

        // Buyer is now active and Supplier is pending (not draft) — the
        // dashboard topbar must offer the switcher to the other profile,
        // even though Supplier isn't approved yet (its own dashboard shows
        // a pending banner rather than blocking access).
        $this->actingAs($user->fresh())->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('Switch to Supplier');

        $this->actingAs($user->fresh())->get(route('supplier.dashboard'))
            ->assertOk()
            ->assertSee('Switch to Buyer');
    }

    /**
     * Before Supplier has finished, EnsureOnboardingComplete (applied to the
     * `/` route) must send a dual-draft account to Supplier onboarding, not
     * Buyer — the previous ordering always picked Buyer first regardless.
     */
    public function test_ensure_onboarding_complete_sends_dual_draft_account_to_supplier_first(): void
    {
        $this->seedBase();
        [$user, $account] = $this->makeDraftBoth('dual-middleware@example.com');

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('supplier.onboarding.profile'));
    }
}
