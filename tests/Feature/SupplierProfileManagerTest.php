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
            'country_id' => $country->id,
            'address' => '1 Industrial Ave',
        ])->assertRedirect(route('supplier.company.profile', ['section' => 'contact']));

        $account->supplierProfile->refresh();
        $this->assertSame('Jane Doe', $account->supplierProfile->contact_person);
        $this->assertSame($country->id, $account->supplierProfile->country_id);

        // Unsupported image format must be rejected with a friendly error, not crash.
        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'logo' => UploadedFile::fake()->create('logo.avif', 10, 'image/avif'),
        ])->assertSessionHasErrors(['logo']);

        $this->actingAs($user)->put(route('supplier.company.profile.media.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('supplier.company.profile', ['section' => 'media']));

        $account->supplierProfile->refresh();
        $this->assertNotNull($account->supplierProfile->logo);

        // Company/Contact sections must not have clobbered each other's fields.
        $this->assertSame('Consolidated Test Co', $account->supplierProfile->display_name);
        $this->assertSame('Jane Doe', $account->supplierProfile->contact_person);
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
        $this->actingAs($user)->delete(route('supplier.company.profile.gallery.destroy', $image));
        $this->assertSame(0, $account->galleryImages()->count());

        $this->actingAs($user)->post(route('supplier.company.profile.videos.store'), [
            'title' => 'Factory Tour',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, $account->videos()->count());

        $video = $account->videos()->first();
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

        $this->assertDatabaseHas('account_documents', [
            'supplier_account_id' => $account->id,
            'document_type_id' => $docType->id,
            'is_current' => true,
        ]);

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

        $cert = $account->certifications()->where('certification_name', 'ISO 9001')->firstOrFail();
        $this->assertSame('pending', $cert->status);

        $this->actingAs($user)->put(route('supplier.company.profile.certifications.update', $cert), [
            'certification_name' => 'ISO 9001:2015',
            'certification_title' => 'Quality Management System',
            'certification_description' => 'Updated description.',
        ])->assertSessionDoesntHaveErrors();
        $this->assertSame('ISO 9001:2015', $cert->fresh()->certification_name);

        // Approved certifications are locked — no further edits accepted.
        $cert->update(['status' => 'approved']);
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
}
