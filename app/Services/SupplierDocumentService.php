<?php

namespace App\Services;

use App\Models\Account;
use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierDocumentService
{
    /**
     * Shared storage folder for account verification documents (suppliers and buyers alike),
     * partitioned by upload date so a single directory never accumulates too many files.
     */
    public const STORAGE_FOLDER = 'verification-documents';

    /**
     * Upload a new document or re-upload a new version.
     */
    public function upload(
        Account $account,
        ?int $documentTypeId,
        UploadedFile $file,
        User $user,
        ?\DateTimeInterface $expiresAt = null,
        ?string $customName = null
    ): SupplierDocument {
        if ($documentTypeId) {
            $docType = DocumentType::findOrFail($documentTypeId);
            // Validate backend format and size restrictions
            $this->validateFileRestrictions($file, $docType);
        } else {
            if (blank($customName)) {
                throw new \InvalidArgumentException('Custom document name is required.');
            }
            // General validation for custom documents: max 10MB, standard web/doc formats
            $fileSizeKb = round($file->getSize() / 1024);
            if ($fileSizeKb > 10240) {
                throw ValidationException::withMessages([
                    'file' => 'File size exceeds the maximum limit of 10MB.',
                ]);
            }
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])) {
                throw ValidationException::withMessages([
                    'file' => 'Invalid file format. Accepted formats: PDF, JPG, JPEG, PNG, DOC, DOCX.',
                ]);
            }
        }

        return DB::transaction(function () use ($account, $documentTypeId, $file, $user, $expiresAt, $customName) {
            if ($documentTypeId) {
                // Lock existing documents for update to avoid race conditions
                SupplierDocument::where('supplier_account_id', $account->id)
                    ->where('document_type_id', $documentTypeId)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->update(['is_current' => false]);
            }

            // Save file, partitioned by upload date (e.g. verification-documents/15_08_2026/...)
            $path = $file->store(self::STORAGE_FOLDER . '/' . now()->format('d_m_Y'), 'public');

            // Create new current document
            $doc = SupplierDocument::create([
                'supplier_account_id' => $account->id,
                'document_type_id'    => $documentTypeId,
                'custom_name'         => $documentTypeId ? null : trim($customName),
                'file_path'           => $path,
                'original_name'       => $file->getClientOriginalName(),
                'mime_type'           => $file->getClientMimeType(),
                'file_size_kb'        => (int) round($file->getSize() / 1024),
                'status'              => 'pending',
                'rejection_reason'    => null,
                'uploaded_by_user_id' => $user->id,
                'expires_at'          => $expiresAt,
                'is_current'          => true,
            ]);

            return $doc;
        });
    }

    /**
     * Verify a document (Admin Gate 1).
     */
    public function verify(SupplierDocument $document, User $admin): void
    {
        if ($document->status === 'verified') {
            return;
        }

        $document->update([
            'status'              => 'verified',
            'rejection_reason'    => null,
            'verified_by_user_id' => $admin->id,
            'verified_at'         => now(),
        ]);
    }

    /**
     * Reject a document (Admin Gate 1). Does NOT consume capability attempt.
     */
    public function reject(SupplierDocument $document, User $admin, string $reason): void
    {
        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Rejection reason is required.']);
        }

        $document->update([
            'status'              => 'rejected',
            'rejection_reason'    => trim($reason),
            'verified_by_user_id' => $admin->id,
            'verified_at'         => now(),
        ]);
    }

    /**
     * Revert document verification or rejection back to pending status.
     */
    public function resetToPending(SupplierDocument $document, User $admin): void
    {
        $document->update([
            'status'              => 'pending',
            'rejection_reason'    => null,
            'verified_by_user_id' => null,
            'verified_at'         => null,
        ]);
    }

    /**
     * Backend file validation against document_types config.
     */
    private function validateFileRestrictions(UploadedFile $file, DocumentType $docType): void
    {
        $maxSizeKb = $docType->max_size_kb ?: 10240; // Default 10MB
        $fileSizeKb = round($file->getSize() / 1024);

        if ($fileSizeKb > $maxSizeKb) {
            throw ValidationException::withMessages([
                'file' => "File size ({$fileSizeKb} KB) exceeds the maximum allowed limit of {$maxSizeKb} KB.",
            ]);
        }

        if (! empty($docType->accepted_formats) && is_array($docType->accepted_formats)) {
            $ext = strtolower($file->getClientOriginalExtension());
            $accepted = array_map('strtolower', $docType->accepted_formats);

            if (! in_array($ext, $accepted)) {
                $allowed = implode(', ', $accepted);
                throw ValidationException::withMessages([
                    'file' => "Invalid format '.{$ext}'. Accepted formats: {$allowed}.",
                ]);
            }
        }
    }
}
