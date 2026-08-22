<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\SupplierDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupplierDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_upload_and_reupload_preserves_history_and_maintains_single_current_document(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        Storage::fake('public');

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization',
            'capability'   => 'supplier',
            'name'         => 'Doc Supplier',
            'email'        => 'docsupplier@example.com',
            'phone'        => '+971500002222',
            'password'     => 'Password123!',
            'organization_display_name' => 'Doc Supplier Corp',
        ]);

        $account = $user->account;

        $docType = DocumentType::create([
            'name'        => 'Trade License',
            'slug'        => 'trade-license',
            'code'        => 'TRADE_LICENSE',
            'is_required' => true,
            'is_active'   => true,
        ]);

        $service = app(SupplierDocumentService::class);

        // Upload v1
        $file1 = UploadedFile::fake()->create('license_v1.pdf', 500, 'application/pdf');
        $doc1  = $service->upload($account, $docType->id, $file1, $user);

        $this->assertTrue((bool)$doc1->is_current);
        $this->assertEquals('pending', $doc1->status);

        // Re-upload v2
        $file2 = UploadedFile::fake()->create('license_v2.pdf', 600, 'application/pdf');
        $doc2  = $service->upload($account, $docType->id, $file2, $user);

        $doc1->refresh();
        $this->assertFalse((bool)$doc1->is_current);
        $this->assertTrue((bool)$doc2->is_current);

        // Assert database count: exactly 1 current document for this account and document type
        $currentCount = SupplierDocument::where('supplier_account_id', $account->id)
            ->where('document_type_id', $docType->id)
            ->where('is_current', true)
            ->count();

        $this->assertEquals(1, $currentCount);
        $this->assertEquals(2, SupplierDocument::where('supplier_account_id', $account->id)->count());
    }
}
