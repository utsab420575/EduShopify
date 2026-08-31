<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BuyerDocument;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mirrors SupplierDocumentService's upload/verify/reject/supersede pattern
 * exactly, targeting BuyerDocument (same underlying account_documents table,
 * scoped to the buyer capability).
 */
class BuyerDocumentService
{
    public function upload(
        Account $account,
        ?int $documentTypeId,
        UploadedFile $file,
        User $user,
        ?\DateTimeInterface $expiresAt = null,
        ?string $customName = null
    ): BuyerDocument {
        if ($documentTypeId) {
            $docType = DocumentType::findOrFail($documentTypeId);
            $this->validateFileRestrictions($file, $docType);
        } else {
            if (blank($customName)) {
                throw new \InvalidArgumentException('Custom document name is required.');
            }
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
                BuyerDocument::where('documentable_id', $account->id)
                    ->where('document_type_id', $documentTypeId)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->update(['is_current' => false]);
            }

            // Same shared storage folder SupplierDocumentService already uses.
            $path = $file->store(\App\Services\SupplierDocumentService::STORAGE_FOLDER.'/'.now()->format('d_m_Y'), 'public');

            return BuyerDocument::create([
                'documentable_id' => $account->id,
                'document_type_id' => $documentTypeId,
                'custom_name' => $documentTypeId ? null : trim($customName),
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size_kb' => (int) round($file->getSize() / 1024),
                'status' => 'pending',
                'rejection_reason' => null,
                'uploaded_by_user_id' => $user->id,
                'expires_at' => $expiresAt,
                'is_current' => true,
            ]);
        });
    }

    public function verify(BuyerDocument $document, User $admin): void
    {
        if ($document->status === 'verified') {
            return;
        }

        $document->update([
            'status' => 'verified',
            'rejection_reason' => null,
            'verified_by_user_id' => $admin->id,
            'verified_at' => now(),
        ]);
    }

    public function reject(BuyerDocument $document, User $admin, string $reason): void
    {
        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Rejection reason is required.']);
        }

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => trim($reason),
            'verified_by_user_id' => $admin->id,
            'verified_at' => now(),
        ]);
    }

    private function validateFileRestrictions(UploadedFile $file, DocumentType $docType): void
    {
        $maxSizeKb = $docType->max_size_kb ?: 10240;
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
