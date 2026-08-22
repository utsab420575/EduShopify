<?php

namespace App\Domain\Supplier\Validators;

use App\Models\Account;
use App\Models\DocumentType;
use App\Models\SupplierDocument;
use Illuminate\Validation\ValidationException;

class SupplierApprovalValidator
{
    /**
     * Run all Supplier capability approval checks.
     * Throws ValidationException if any check fails.
     */
    public function validate(Account $account): void
    {
        $this->validateProfileComplete($account);
        $this->validateRequiredDocumentsPresent($account);
        $this->validateRequiredDocumentsVerified($account);
        $this->validateDocumentsNotExpired($account);
    }

    /**
     * 1. Supplier profile must exist and be completed.
     */
    public function validateProfileComplete(Account $account): void
    {
        $profile = $account->supplierProfile;

        if (! $profile || ! $profile->isComplete()) {
            throw ValidationException::withMessages([
                'profile' => 'Supplier capability approval requires a complete Supplier profile.',
            ]);
        }

        // Require at least one supplier_supplier_type pivot entry
        if (! $account->supplierTypes()->exists()) {
            throw ValidationException::withMessages([
                'supplier_types' => 'Supplier profile must have at least one business type selected.',
            ]);
        }
    }

    /**
     * 2. Every active required document type must have a current document uploaded.
     */
    public function validateRequiredDocumentsPresent(Account $account): void
    {
        $requiredTypeIds = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->pluck('id');

        foreach ($requiredTypeIds as $typeId) {
            $hasCurrentDoc = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrentDoc) {
                $docType = DocumentType::find($typeId);
                $typeName = $docType ? $docType->name : "Type #{$typeId}";

                throw ValidationException::withMessages([
                    'documents' => "Supplier approval failed: Required document '{$typeName}' is missing or rejected.",
                ]);
            }
        }
    }

    /**
     * 3. Every required current document must have status = verified.
     */
    public function validateRequiredDocumentsVerified(Account $account): void
    {
        $requiredTypeIds = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->pluck('id');

        foreach ($requiredTypeIds as $typeId) {
            $isVerified = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->where('status', 'verified')
                ->exists();

            if (! $isVerified) {
                $docType = DocumentType::find($typeId);
                $typeName = $docType ? $docType->name : "Type #{$typeId}";

                throw ValidationException::withMessages([
                    'documents' => "Supplier approval failed: Required document '{$typeName}' is not yet verified by an administrator.",
                ]);
            }
        }
    }

    /**
     * 4. No required current document must be expired.
     */
    public function validateDocumentsNotExpired(Account $account): void
    {
        $requiredTypeIds = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->pluck('id');

        foreach ($requiredTypeIds as $typeId) {
            $doc = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->where('status', 'verified')
                ->first();

            if ($doc && $doc->expires_at && $doc->expires_at->isPast()) {
                $typeName = $doc->documentType?->name ?? "Type #{$typeId}";

                throw ValidationException::withMessages([
                    'documents' => "Supplier approval failed: Document '{$typeName}' has expired on {$doc->expires_at->format('Y-m-d')}.",
                ]);
            }
        }
    }
}
