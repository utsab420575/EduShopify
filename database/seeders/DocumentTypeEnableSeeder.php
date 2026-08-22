<?php

namespace Database\Seeders;

use App\Models\CapabilityType;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use Illuminate\Database\Seeder;

class DocumentTypeEnableSeeder extends Seeder
{
    public function run(): void
    {
        $supplierCap = CapabilityType::where('code', 'supplier')->first();

        if ($supplierCap) {
            $requiredSlugs = [
                'trade-license',
                'company-registration-certificate',
                'tax-certificate',
            ];
            $docTypes = DocumentType::all();
            foreach ($docTypes as $docType) {
                DocumentTypeEnable::firstOrCreate([
                    'document_type_id'   => $docType->id,
                    'capability_type_id' => $supplierCap->id,
                ], [
                    'is_required'        => in_array($docType->slug, $requiredSlugs, true),
                ]);
            }
        }
    }
}
