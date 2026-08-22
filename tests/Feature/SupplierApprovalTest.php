<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Models\SupplierType;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\CapabilityReviewService;
use App\Services\SupplierDocumentService;
use App\Services\SupplierProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SupplierApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_fails_when_required_document_is_missing_or_unverified(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        Storage::fake('public');

        $admin = User::factory()->create(['status' => 'active']);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization',
            'capability'   => 'supplier',
            'name'         => 'Supplier One',
            'email'        => 'supplier1@example.com',
            'phone'        => '+971500003333',
            'password'     => 'Password123!',
            'organization_display_name' => 'Supplier One Inc',
        ]);

        $account = $user->account;

        $country      = \App\Models\Country::create(['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Manufacturer', 'slug' => 'manufacturer', 'code' => 'MFG', 'is_active' => true]);
        $docType      = DocumentType::create(['name' => 'Trade License', 'slug' => 'trade-license', 'code' => 'TL', 'is_required' => true, 'is_active' => true]);

        // SupplierApprovalValidator determines "required" dynamically through
        // document_type_enables, not the plain document_types.is_required
        // column — without this row the validator finds nothing to require.
        $supplierCapabilityType = \App\Models\CapabilityType::where('code', 'supplier')->firstOrFail();
        \App\Models\DocumentTypeEnable::create([
            'document_type_id'   => $docType->id,
            'capability_type_id' => $supplierCapabilityType->id,
            'is_required'        => true,
        ]);

        // Complete profile
        app(SupplierProfileService::class)->completeProfile($account, [
            'display_name'      => 'Supplier One Inc',
            'legal_name'        => 'Supplier One Incorporated',
            'legal_entity_type' => 'Corporation',
            'supplier_type_ids' => [$supplierType->id],
            'contact_person'    => 'John Supplier',
            'contact_email'     => 'supplier1@example.com',
            'country_id'        => $country->id,
            'address'           => '123 Business Way',
        ]);

        // Upload required document (pending verification)
        $file = UploadedFile::fake()->create('trade.pdf', 300, 'application/pdf');
        $doc  = app(SupplierDocumentService::class)->upload($account, $docType->id, $file, $user);

        // Move capability status to pending for review
        $cap = $account->supplierCapability;
        $cap->update(['status' => 'pending', 'application_attempts' => 1]);

        $reviewService = app(CapabilityReviewService::class);

        // Attempt approval when document is still pending verification -> expect exception
        $this->expectException(ValidationException::class);
        $reviewService->approve($cap, $admin);
    }

    public function test_approval_succeeds_when_profile_complete_and_all_required_documents_verified(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        Storage::fake('public');

        $admin = User::factory()->create(['status' => 'active']);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization',
            'capability'   => 'supplier',
            'name'         => 'Supplier Two',
            'email'        => 'supplier2@example.com',
            'phone'        => '+971500004444',
            'password'     => 'Password123!',
            'organization_display_name' => 'Supplier Two Ltd',
        ]);

        $account = $user->account;

        $country      = \App\Models\Country::create(['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $supplierType = SupplierType::create(['name' => 'Distributor', 'slug' => 'distributor', 'code' => 'DIST', 'is_active' => true]);
        $docType      = DocumentType::create(['name' => 'Trade License', 'slug' => 'trade-license-2', 'code' => 'TL2', 'is_required' => true, 'is_active' => true]);

        $supplierCapabilityType = \App\Models\CapabilityType::where('code', 'supplier')->firstOrFail();
        \App\Models\DocumentTypeEnable::create([
            'document_type_id'   => $docType->id,
            'capability_type_id' => $supplierCapabilityType->id,
            'is_required'        => true,
        ]);

        // Complete profile
        app(SupplierProfileService::class)->completeProfile($account, [
            'display_name'      => 'Supplier Two Ltd',
            'legal_name'        => 'Supplier Two Limited',
            'legal_entity_type' => 'Limited Company',
            'supplier_type_ids' => [$supplierType->id],
            'contact_person'    => 'Jane Supplier',
            'contact_email'     => 'supplier2@example.com',
            'country_id'        => $country->id,
            'address'           => '456 Commercial St',
        ]);

        // Upload & verify document
        $file = UploadedFile::fake()->create('trade.pdf', 300, 'application/pdf');
        $doc  = app(SupplierDocumentService::class)->upload($account, $docType->id, $file, $user);
        app(SupplierDocumentService::class)->verify($doc, $admin);

        // Capability pending
        $cap = $account->supplierCapability;
        $cap->update(['status' => 'pending', 'application_attempts' => 1]);

        // Approve capability
        app(CapabilityReviewService::class)->approve($cap, $admin);

        $cap->refresh();
        $this->assertEquals('active', $cap->status);
        $this->assertNotNull($cap->activated_at);
    }
}
