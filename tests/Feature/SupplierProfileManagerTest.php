<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CapabilityType;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use App\Models\Exhibition;
use App\Models\State;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SupplierType;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The Supplier "Business Profile" dashboard page consolidates Company Info,
 * Contact, Media, Gallery & Videos, Locations & Service Areas, Business
 * Hours, Exhibitions, Documents, Services, and Achievements & Certifications
 * into one expandable accordion page (docs/AI/design.md §43), each section
 * saving through its own plain Controller + Form Request route per
 * ARCHITECTURE.md Rule 1 (no backend Livewire). This test drives every
 * add/edit/delete action across all 10 sections to prove no functionality
 * was lost migrating off the legacy Livewire component.
 */
class SupplierProfileManagerTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveSupplier(string $email): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual',
            'capability'   => 'supplier',
            'name'         => 'Profile Manager Test User',
            'email'        => $email,
            'phone'        => '+1555000' . random_int(1000, 9999),
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
            'name' => 'Free Plan', 'slug' => 'free-plan-' . uniqid(),
            'billing_type' => 'free', 'price' => 0, 'currency_code' => 'USD',
            'is_free' => true, 'is_active' => true,
            'max_active_listings' => 10, 'max_monthly_quotations' => 50, 'rfq_delay_minutes' => 0,
        ]);
        $subscription = Subscription::create([
            'supplier_account_id' => $account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $user->id, 'provider' => 'free', 'status' => 'pending',
        ]);
        $subscription->activate();

        return $user->fresh();
    }

    public function test_the_consolidated_page_replaces_the_old_business_profile_submenu(): void
    {
        $user = $this->makeActiveSupplier('spm-sidebar@example.com');

        $response = $this->actingAs($user)->get(route('supplier.company.profile'));

        $response->assertOk();
        $response->assertSee('Business Profile');
        // Every accordion section must be present on the one page.
        $response->assertSee('Company Information');
        $response->assertSee('Contact Information');
        $response->assertSee('Media & Branding', false);
        $response->assertSee('Gallery & Videos', false);
        $response->assertSee('Locations & Service Areas', false);
        $response->assertSee('Business Hours');
        $response->assertSee('Exhibitions');
        $response->assertSee('Documents & Verification', false);
        $response->assertSee('Services');
        $response->assertSee('Achievements &amp; Certifications', false);

        // The old separate pages must be gone.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('supplier.company.documents'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('supplier.company.service-areas'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('supplier.company.business-hours'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('supplier.company.gallery'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('supplier.company.exhibitions'));

        // Backend must not use Livewire (ARCHITECTURE.md Rule 1).
        $response->assertDontSee('wire:click', false);
        $response->assertDontSee('wire:model', false);
    }

    public function test_company_and_contact_and_media_sections_save_independently(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-company@example.com');
        $account = $user->account;

        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer-spm', 'code' => 'MFGSPM', 'is_active' => true]);
        $category = Category::create(['name' => 'Lab Equipment', 'slug' => 'lab-equipment-spm', 'type' => 'product', 'approval_status' => 'approved', 'is_active' => true]);
        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);

        $this->actingAs($user)->put(route('supplier.company.profile.company.update'), [
            'display_name' => 'Consolidated Test Co',
            'legal_name' => 'Consolidated Test Co LLC',
            'supplier_type_ids' => [$supplierType->id],
            'category_ids' => [$category->id],
        ])->assertRedirect(route('supplier.company.profile', ['section' => 'company']));

        $account->supplierProfile->refresh();
        $this->assertSame('Consolidated Test Co', $account->supplierProfile->display_name);
        $this->assertTrue($account->supplierTypes()->where('supplier_types.id', $supplierType->id)->exists());
        $this->assertDatabaseHas('supplier_categories', ['supplier_account_id' => $account->id, 'category_id' => $category->id, 'is_active' => true]);

        $this->actingAs($user)->put(route('supplier.company.profile.contact.update'), [
            'contact_person' => 'Jane Doe',
            'contact_email' => 'spm-company@example.com',
            'contact_phone' => '+15551234567',
        ])->assertRedirect(route('supplier.company.profile', ['section' => 'contact']));

        $account->supplierProfile->refresh();
        $this->assertSame('Jane Doe', $account->supplierProfile->contact_person);
        $this->assertSame('spm-company@example.com', $account->supplierProfile->contact_email);
        $this->assertSame('+15551234567', $account->supplierProfile->contact_phone);

        // Unsupported image format must be rejected with a friendly error, not crash.
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'logo' => UploadedFile::fake()->create('logo.avif', 10, 'image/avif'),
        ])->assertSessionHasErrors(['logo']);

        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('supplier.company.profile', ['section' => 'media']));

        $account->supplierProfile->refresh();
        $firstLogo = $account->supplierProfile->logo;
        $this->assertNotNull($firstLogo);
        // Stored under a dated folder: supplier-logos/dd_month_year/<name>.
        $this->assertStringStartsWith('supplier-logos/'.strtolower(now()->format('d_F_Y')).'/', $firstLogo);
        Storage::disk('public')->assertExists($firstLogo);

        // Replacing the logo deletes the previous file.
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'logo' => UploadedFile::fake()->image('logo2.png'),
        ])->assertSessionDoesntHaveErrors();

        $account->supplierProfile->refresh();
        $this->assertNotSame($firstLogo, $account->supplierProfile->logo);
        Storage::disk('public')->assertMissing($firstLogo);
        Storage::disk('public')->assertExists($account->supplierProfile->logo);

        // Company/Contact sections must not have clobbered each other's fields.
        $this->assertSame('Consolidated Test Co', $account->supplierProfile->display_name);
        $this->assertSame('Jane Doe', $account->supplierProfile->contact_person);
    }

    public function test_banner_is_cropped_server_side_and_can_be_recropped_from_the_original(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-banner@example.com');
        $disk = Storage::disk('public');

        // Upload an original with a crop box (in original pixels).
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'banner' => UploadedFile::fake()->image('hero.jpg', 2200, 1400),
            'banner_crop' => ['x' => 100, 'y' => 200, 'width' => 2000, 'height' => 500],
        ])->assertSessionDoesntHaveErrors();

        $profile = $user->account->supplierProfile->fresh();
        $folder = 'supplier-banners/'.strtolower(now()->format('d_F_Y')).'/';
        $this->assertStringStartsWith($folder, $profile->banner);
        $this->assertStringStartsWith($folder, $profile->banner_original);
        $this->assertSame(['x' => 100, 'y' => 200, 'width' => 2000, 'height' => 500], $profile->banner_crop);

        // Cropped by Intervention, then scaled to the 1760px display width.
        [$w, $h] = getimagesize($disk->path($profile->banner));
        $this->assertSame([1760, 440], [$w, $h]);
        // The original is kept uncropped.
        $this->assertSame([2200, 1400], array_slice(getimagesize($disk->path($profile->banner_original)), 0, 2));

        // Re-crop with only a new box: same original, new cropped file, old one deleted.
        $firstBanner = $profile->banner;
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'banner_crop' => ['x' => 0, 'y' => 0, 'width' => 5000, 'height' => 800],   // clamped to the image
        ])->assertSessionDoesntHaveErrors();

        $profile->refresh();
        $this->assertNotSame($firstBanner, $profile->banner);
        $disk->assertMissing($firstBanner);
        $disk->assertExists($profile->banner_original);
        $this->assertSame(['x' => 0, 'y' => 0, 'width' => 2200, 'height' => 800], $profile->banner_crop);

        // Uploading a new original replaces both files.
        $oldOriginal = $profile->banner_original;
        $oldBanner = $profile->banner;
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'banner' => UploadedFile::fake()->image('hero2.jpg', 1800, 900),
            'banner_crop' => ['x' => 0, 'y' => 100, 'width' => 1800, 'height' => 400],
        ])->assertSessionDoesntHaveErrors();

        $profile->refresh();
        $disk->assertMissing($oldOriginal);
        $disk->assertMissing($oldBanner);
        $disk->assertExists($profile->banner);
        $disk->assertExists($profile->banner_original);
    }

    public function test_banner_fit_defaults_to_cover_and_saves_without_a_recrop(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-fit@example.com');

        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'banner' => UploadedFile::fake()->image('hero.jpg', 2200, 1000),
            'banner_crop' => ['x' => 0, 'y' => 0, 'width' => 2200, 'height' => 500],
        ])->assertSessionDoesntHaveErrors();

        $profile = $user->account->supplierProfile->fresh();
        $this->assertSame('cover', $profile->banner_fit);
        $banner = $profile->banner;

        // Changing only the fit keeps the same banner file.
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), ['banner_fit' => 'contain'])
            ->assertSessionDoesntHaveErrors();
        $profile->refresh();
        $this->assertSame('contain', $profile->banner_fit);
        $this->assertSame($banner, $profile->banner);

        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), ['banner_fit' => 'stretch'])
            ->assertSessionHasErrors('banner_fit');
        $this->assertSame('contain', $profile->fresh()->banner_fit);
    }

    public function test_gallery_and_video_add_and_remove(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-gallery@example.com');
        $account = $user->account;

        $this->actingAs($user)->post(route('supplier.company.profile.gallery.store'), [
            'photos' => [UploadedFile::fake()->image('g1.png')],
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, $account->galleryImages()->count());

        $image = $account->galleryImages()->first();
        $this->assertTrue($image->is_active);

        // Update photo details and set inactive
        $this->actingAs($user)->put(route('supplier.company.profile.gallery.update', $image), [
            'caption' => 'Cleanroom Line',
            'alt_text' => 'Cleanroom Photo',
            'is_active' => '0',
        ])->assertSessionDoesntHaveErrors();

        $image->refresh();
        $this->assertSame('Cleanroom Line', $image->caption);
        $this->assertSame('Cleanroom Photo', $image->alt_text);
        $this->assertFalse($image->is_active);

        // Update photo back to active
        $this->actingAs($user)->put(route('supplier.company.profile.gallery.update', $image), [
            'caption' => 'Cleanroom Line',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $image->refresh();
        $this->assertTrue($image->is_active);

        $this->actingAs($user)->delete(route('supplier.company.profile.gallery.destroy', $image));
        $this->assertSame(0, $account->galleryImages()->count());

        $this->actingAs($user)->post(route('supplier.company.profile.videos.store'), [
            'title' => 'Factory Tour',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, $account->videos()->count());

        $video = $account->videos()->first();
        $this->assertTrue($video->is_active);

        // Update video title and set inactive
        $this->actingAs($user)->put(route('supplier.company.profile.videos.update', $video), [
            'title' => 'Updated Factory Tour',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_active' => '0',
        ])->assertSessionDoesntHaveErrors();

        $video->refresh();
        $this->assertSame('Updated Factory Tour', $video->title);
        $this->assertFalse($video->is_active);

        // Update video back to active
        $this->actingAs($user)->put(route('supplier.company.profile.videos.update', $video), [
            'title' => 'Updated Factory Tour',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $video->refresh();
        $this->assertTrue($video->is_active);

        $this->actingAs($user)->delete(route('supplier.company.profile.videos.destroy', $video));
        $this->assertSame(0, $account->videos()->count());
    }

    public function test_service_area_add_edit_delete_and_make_primary(): void
    {
        $user = $this->makeActiveSupplier('spm-servicearea@example.com');
        $account = $user->account;

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $state2 = State::create(['country_id' => $country->id, 'name' => 'Abu Dhabi', 'is_active' => true]);

        $this->actingAs($user)->post(route('supplier.company.profile.service-areas.store'), [
            'country_id' => $country->id,
            'radius_km' => 50,
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, $account->serviceAreas()->count());

        $area = $account->serviceAreas()->first();

        // Add a second, then edit the first.
        $this->actingAs($user)->post(route('supplier.company.profile.service-areas.store'), [
            'country_id' => $country->id,
            'state_id' => $state2->id,
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame(2, $account->serviceAreas()->count());

        $this->actingAs($user)->put(route('supplier.company.profile.service-areas.update', $area), [
            'country_id' => $country->id,
            'state_id' => $state->id,
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame($state->id, $area->fresh()->state_id);

        $second = $account->serviceAreas()->where('id', '!=', $area->id)->first();
        $this->actingAs($user)->post(route('supplier.company.profile.service-areas.primary', $second));
        $this->assertFalse((bool) $area->fresh()->is_primary);
        $this->assertTrue((bool) $second->fresh()->is_primary);

        $this->actingAs($user)->delete(route('supplier.company.profile.service-areas.destroy', $area));
        $this->assertSame(1, $account->serviceAreas()->count());
    }

    public function test_worldwide_service_area_with_preferred_local_hub(): void
    {
        $user = $this->makeActiveSupplier('spm-global-servicearea@example.com');
        $account = $user->account;

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);

        // 1. Add Worldwide service area even when browser sends '0' for hidden state/city
        $this->actingAs($user)->post(route('supplier.company.profile.service-areas.store'), [
            'country_id' => 'global',
            'state_id' => '0',
            'city_id' => '0',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(1, $account->serviceAreas()->count());
        $globalArea = $account->serviceAreas()->first();
        $this->assertSame('global', $globalArea->area_level);
        $this->assertNull($globalArea->country_id);
        $this->assertNull($globalArea->state_id);
        $this->assertNull($globalArea->city_id);
        $this->assertTrue($globalArea->isGlobal());
        $this->assertTrue($account->fresh()->isWorldwideSupplier());

        // 2. Add a preferred local hub (e.g., Dubai, UAE) marked as primary
        $this->actingAs($user)->post(route('supplier.company.profile.service-areas.store'), [
            'country_id' => $country->id,
            'state_id' => $state->id,
            'is_primary' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(2, $account->serviceAreas()->count());
        // Worldwide status remains true even with preferred local hub
        $this->assertTrue($account->fresh()->isWorldwideSupplier());

        $hub = $account->serviceAreas()->where('area_level', 'state')->first();
        $this->assertNotNull($hub);
        $this->assertSame($country->id, $hub->country_id);
        $this->assertSame($state->id, $hub->state_id);
        $this->assertTrue((bool) $hub->is_primary);

        // 3. Edit hub
        $state2 = State::create(['country_id' => $country->id, 'name' => 'Abu Dhabi', 'is_active' => true]);
        $this->actingAs($user)->put(route('supplier.company.profile.service-areas.update', $hub), [
            'country_id' => $country->id,
            'state_id' => $state2->id,
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame($state2->id, $hub->fresh()->state_id);

        // 4. Update the hub to be worldwide as well
        $this->actingAs($user)->put(route('supplier.company.profile.service-areas.update', $hub), [
            'country_id' => 'global',
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame('global', $hub->fresh()->area_level);
        $this->assertNull($hub->fresh()->country_id);
    }

    public function test_physical_location_add_edit_delete_and_make_primary(): void
    {
        $user = $this->makeActiveSupplier('spm-locations@example.com');
        $account = $user->account;

        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Dubai', 'is_active' => true]);
        $state2 = State::create(['country_id' => $country->id, 'name' => 'Abu Dhabi', 'is_active' => true]);

        // Add first physical location (should auto-become primary)
        $this->actingAs($user)->post(route('supplier.company.profile.locations.store'), [
            'location_type' => 'head_office',
            'label' => 'Main Headquarters',
            'address_line_1' => 'Sheikh Zayed Road, Tower 1',
            'country_id' => $country->id,
            'state_id' => $state->id,
            'contact_name' => 'John HQ',
            'phone' => '+97140001111',
            'is_primary' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(1, $account->locations()->count());
        $loc1 = $account->locations()->first();
        $this->assertTrue((bool) $loc1->is_primary);
        $this->assertSame('Main Headquarters', $loc1->label);

        // Add second physical location as branch
        $this->actingAs($user)->post(route('supplier.company.profile.locations.store'), [
            'location_type' => 'warehouse',
            'label' => 'Abu Dhabi Distribution Center',
            'address_line_1' => 'Industrial City 1',
            'country_id' => $country->id,
            'state_id' => $state2->id,
            'contact_name' => 'Sarah Logistics',
            'phone' => '+97120002222',
            'is_primary' => '0',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(2, $account->locations()->count());
        $loc2 = $account->locations()->where('id', '!=', $loc1->id)->first();
        $this->assertFalse((bool) $loc2->is_primary);

        // Update second location to primary
        $this->actingAs($user)->post(route('supplier.company.profile.locations.primary', $loc2));
        $this->assertTrue((bool) $loc2->fresh()->is_primary);
        $this->assertFalse((bool) $loc1->fresh()->is_primary);

        // Update first location details
        $this->actingAs($user)->put(route('supplier.company.profile.locations.update', $loc1), [
            'location_type' => 'showroom',
            'label' => 'Downtown Showroom',
            'address_line_1' => 'Sheikh Zayed Road, Suite 200',
            'country_id' => $country->id,
            'state_id' => $state->id,
            'is_active' => '0',
        ])->assertSessionDoesntHaveErrors();

        $loc1->refresh();
        $this->assertSame('showroom', $loc1->location_type);
        $this->assertSame('Downtown Showroom', $loc1->label);
        $this->assertFalse((bool) $loc1->is_active);

        // Delete second location
        $this->actingAs($user)->delete(route('supplier.company.profile.locations.destroy', $loc2));
        $this->assertSame(1, $account->locations()->count());
    }

    public function test_business_hours_save(): void
    {
        $user = $this->makeActiveSupplier('spm-hours@example.com');
        $account = $user->account;

        $days = [];
        foreach (range(0, 6) as $d) {
            $days[$d] = [
                'is_open' => in_array($d, [1, 2, 3, 4, 5], true) ? '1' : '0',
                'open_time' => '08:00',
                'close_time' => '18:00',
            ];
        }

        $this->actingAs($user)->put(route('supplier.company.profile.business-hours.update'), ['days' => $days])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('business_hours', [
            'supplier_account_id' => $account->id,
            'account_location_id' => null,
            'day_of_week' => 1,
            'is_open' => true,
            'open_time' => '08:00',
        ]);
    }

    public function test_exhibition_join_and_leave(): void
    {
        $user = $this->makeActiveSupplier('spm-exhibition@example.com');
        $account = $user->account;

        $exhibition = Exhibition::create(['name' => 'EduExpo SPM', 'slug' => 'eduexpo-spm', 'is_active' => true]);

        $this->actingAs($user)->post(route('supplier.company.profile.exhibitions.join', $exhibition), [
            'booth_number' => 'A-12',
            'participation_year' => '2026',
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('exhibition_supplier', [
            'exhibition_id' => $exhibition->id,
            'supplier_account_id' => $account->id,
            'booth_number' => 'A-12',
        ]);

        $this->actingAs($user)->delete(route('supplier.company.profile.exhibitions.leave', $exhibition));
        $this->assertDatabaseMissing('exhibition_supplier', [
            'exhibition_id' => $exhibition->id,
            'supplier_account_id' => $account->id,
        ]);
    }

    public function test_document_upload_versioning_and_delete(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-documents@example.com');
        $account = $user->account;

        $docType = DocumentType::create(['name' => 'Trade License', 'slug' => 'trade-license-spm', 'code' => 'TLSPM', 'is_required' => true, 'is_active' => true]);
        DocumentTypeEnable::create([
            'document_type_id' => $docType->id,
            'capability_type_id' => CapabilityType::where('code', 'supplier')->value('id'),
            'is_required' => true,
        ]);

        $this->actingAs($user)->post(route('supplier.company.profile.documents.store'), [
            'document_type_id' => $docType->id,
            'file' => UploadedFile::fake()->create('trade.pdf', 200, 'application/pdf'),
        ])->assertSessionDoesntHaveErrors();

        // Uploads from Company Profile › Documents are auto-approved ('verified');
        // no admin is recorded as the verifier.
        $this->assertDatabaseHas('account_documents', [
            'supplier_account_id' => $account->id,
            'document_type_id' => $docType->id,
            'is_current' => true,
            'status' => 'verified',
            'verified_by_user_id' => null,
        ]);
        $this->assertNotNull($account->supplierDocuments()->where('is_current', true)->value('verified_at'));

        // Re-uploading the same document type must version the old one out,
        // not leave two "current" rows.
        $this->actingAs($user)->post(route('supplier.company.profile.documents.store'), [
            'document_type_id' => $docType->id,
            'file' => UploadedFile::fake()->create('trade-v2.pdf', 200, 'application/pdf'),
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(1, $account->supplierDocuments()->where('document_type_id', $docType->id)->where('is_current', true)->count());
        $this->assertSame(2, $account->supplierDocuments()->where('document_type_id', $docType->id)->count());

        $current = $account->supplierDocuments()->where('is_current', true)->first();
        $this->actingAs($user)->delete(route('supplier.company.profile.documents.destroy', $current));
        $this->assertDatabaseMissing('account_documents', ['id' => $current->id]);
    }

    public function test_achievement_request_and_undo(): void
    {
        $user = $this->makeActiveSupplier('spm-achievement@example.com');
        $account = $user->account;

        $achievement = \App\Models\Achievement::create([
            'name' => 'Founding Supplier SPM', 'slug' => 'founding-supplier-spm', 'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('supplier.company.profile.achievements.request', $achievement))
            ->assertRedirect(route('supplier.company.profile', ['section' => 'achievements']));

        $claim = $account->accountAchievements()->where('achievement_id', $achievement->id)->firstOrFail();
        $this->assertSame('pending', $claim->status);

        // Requesting again while a claim already exists must not duplicate it.
        $this->actingAs($user)->post(route('supplier.company.profile.achievements.request', $achievement));
        $this->assertSame(1, $account->accountAchievements()->where('achievement_id', $achievement->id)->count());

        $this->actingAs($user)->delete(route('supplier.company.profile.achievements.undo', $claim));
        $this->assertDatabaseMissing('account_achievements', ['id' => $claim->id]);

        // Once withdrawn, the achievement can be requested again.
        $this->actingAs($user)->post(route('supplier.company.profile.achievements.request', $achievement));
        $this->assertSame(1, $account->accountAchievements()->where('achievement_id', $achievement->id)->count());
    }

    public function test_certification_store_update_and_cancel(): void
    {
        Storage::fake('public');
        $user = $this->makeActiveSupplier('spm-certification@example.com');
        $account = $user->account;

        $this->actingAs($user)->post(route('supplier.company.profile.certifications.store'), [
            'certification_name' => 'ISO 9001',
            'certification_title' => 'Quality Management System',
            'certification_description' => 'Certified for quality management.',
        ])->assertSessionDoesntHaveErrors();

        // Approved automatically on upload, with no admin recorded as approver.
        $cert = $account->certifications()->where('certification_name', 'ISO 9001')->firstOrFail();
        $this->assertSame('approved', $cert->status);
        $this->assertNotNull($cert->approved_at);
        $this->assertNull($cert->approved_by_user_id);

        // Auto-approved certifications stay editable by the supplier.
        $this->actingAs($user)->put(route('supplier.company.profile.certifications.update', $cert), [
            'certification_name' => 'ISO 9001:2015',
            'certification_title' => 'Quality Management System',
            'certification_description' => 'Updated description.',
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame('ISO 9001:2015', $cert->fresh()->certification_name);

        // Admin-approved certifications are locked — no further edits accepted.
        $cert->update(['status' => 'approved', 'approved_by_user_id' => $user->id]);
        $this->actingAs($user)->put(route('supplier.company.profile.certifications.update', $cert), [
            'certification_name' => 'Should Not Apply',
            'certification_title' => 'Quality Management System',
            'certification_description' => 'Should not apply.',
        ]);
        $this->assertSame('ISO 9001:2015', $cert->fresh()->certification_name);

        // ...and cannot be cancelled either.
        $this->actingAs($user)->delete(route('supplier.company.profile.certifications.destroy', $cert));
        $this->assertNotNull($cert->fresh());

        $cert->update(['status' => 'pending']);
        $this->actingAs($user)->delete(route('supplier.company.profile.certifications.destroy', $cert));
        $this->assertNull($cert->fresh());
    }

    public function test_service_add_edit_delete(): void
    {
        $user = $this->makeActiveSupplier('spm-service@example.com');
        $account = $user->account;

        $this->actingAs($user)->post(route('supplier.company.profile.services.store'), [
            'title' => 'Custom Packaging',
            'status' => 'active',
            'sort_order' => 1,
        ])->assertSessionDoesntHaveErrors();

        $service = $account->services()->where('title', 'Custom Packaging')->firstOrFail();

        $this->actingAs($user)->put(route('supplier.company.profile.services.update', $service), [
            'title' => 'Custom Packaging Updated',
            'status' => 'inactive',
            'sort_order' => 2,
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame('Custom Packaging Updated', $service->fresh()->title);
        $this->assertSame('inactive', $service->fresh()->status);

        $this->actingAs($user)->delete(route('supplier.company.profile.services.destroy', $service));
        $this->assertNull($service->fresh());
    }

    public function test_services_can_be_reordered_by_drag_and_drop(): void
    {
        $user = $this->makeActiveSupplier('spm-service-order@example.com');
        $account = $user->account;

        // Added without an explicit order → appended 1, 2, 3.
        foreach (['Alpha', 'Beta', 'Gamma'] as $title) {
            $this->actingAs($user)->post(route('supplier.company.profile.services.store'), ['title' => $title, 'status' => 'active'])
                ->assertSessionDoesntHaveErrors();
        }
        $ids = $account->services()->orderBy('sort_order')->pluck('id', 'title');
        $this->assertSame([1, 2, 3], $account->services()->orderBy('sort_order')->pluck('sort_order')->all());

        // Drag Gamma (order 3) to the top.
        $this->actingAs($user)->postJson(route('supplier.company.profile.services.reorder'), [
            'ids' => [$ids['Gamma'], $ids['Alpha'], $ids['Beta']],
        ])->assertOk()->assertJsonPath('order.'.$ids['Gamma'], 1);

        $this->assertSame(['Gamma', 'Alpha', 'Beta'], $account->services()->orderBy('sort_order')->pluck('title')->all());
        $this->assertSame([1, 2, 3], $account->services()->orderBy('sort_order')->pluck('sort_order')->all());

        // An incomplete list, or one including another supplier's service, is rejected unchanged.
        $other = $this->makeActiveSupplier('spm-service-order-other@example.com');
        $foreign = $other->account->services()->create(['title' => 'Foreign', 'status' => 'active', 'sort_order' => 1]);

        $this->actingAs($user)->postJson(route('supplier.company.profile.services.reorder'), [
            'ids' => [$ids['Alpha'], $ids['Beta']],
        ])->assertStatus(422);
        $this->actingAs($user)->postJson(route('supplier.company.profile.services.reorder'), [
            'ids' => [$ids['Alpha'], $ids['Beta'], $foreign->id],
        ])->assertStatus(422);

        $this->assertSame(['Gamma', 'Alpha', 'Beta'], $account->services()->orderBy('sort_order')->pluck('title')->all());
        $this->assertSame(1, $foreign->fresh()->sort_order);
    }

    public function test_social_links_add_edit_and_delete(): void
    {
        $user = $this->makeActiveSupplier('spm-social-links@example.com');
        $account = $user->account;

        $platform = \App\Models\SocialPlatform::create([
            'name' => 'Instagram',
            'slug' => 'instagram-' . uniqid(),
            'is_active' => true,
        ]);

        // 1. Add social link
        $this->actingAs($user)->post(route('supplier.company.profile.social-links.store'), [
            'social_platform_id' => $platform->id,
            'url' => 'https://www.instagram.com/mybrand',
            'handle' => '@mybrand',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors()
          ->assertRedirect(route('supplier.company.profile', ['section' => 'social-links']));

        $this->assertSame(1, $account->socialLinks()->count());
        $link = $account->socialLinks()->firstOrFail();
        $this->assertSame('https://www.instagram.com/mybrand', $link->url);
        $this->assertTrue($link->is_active);

        // 2. Edit social link - update handle & set inactive (is_active = 0)
        $this->actingAs($user)->put(route('supplier.company.profile.social-links.update', $link), [
            'social_platform_id' => $platform->id,
            'url' => 'https://www.instagram.com/mybrand_updated',
            'handle' => '@mybrand_updated',
            'is_active' => '0',
        ])->assertSessionDoesntHaveErrors()
          ->assertRedirect(route('supplier.company.profile', ['section' => 'social-links']));

        $link->refresh();
        $this->assertSame('https://www.instagram.com/mybrand_updated', $link->url);
        $this->assertSame('@mybrand_updated', $link->handle);
        $this->assertFalse($link->is_active);

        // 3. Edit social link - reactivate (is_active = 1)
        $this->actingAs($user)->put(route('supplier.company.profile.social-links.update', $link), [
            'social_platform_id' => $platform->id,
            'url' => 'https://www.instagram.com/mybrand_updated',
            'handle' => '@mybrand_updated',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertTrue($link->fresh()->is_active);

        // 4. Delete social link
        $this->actingAs($user)->delete(route('supplier.company.profile.social-links.destroy', $link))
          ->assertRedirect(route('supplier.company.profile', ['section' => 'social-links']));

        $this->assertSame(0, $account->socialLinks()->count());
    }
}
