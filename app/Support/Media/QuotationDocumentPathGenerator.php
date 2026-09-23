<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores Quotation documents structured by date:
 * storage/QuotationDocument/{dd_mm_yyyy}/{filename}
 *
 * Example:
 * storage/QuotationDocument/23_09_2026/21_6ab35c_proposal.pdf
 */
class QuotationDocumentPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media) . '/responsive-images/';
    }

    private function basePath(Media $media): string
    {
        $date = ($media->created_at ?? now())->format('d_m_Y');

        if ($media->collection_name === 'gallery') {
            return 'QuotationGallery/' . $date;
        }

        return 'QuotationDocument/' . $date;
    }

    /**
     * Generates a structured, unique filename:
     * "{supplier_account_id}_{uniqid}_{sanitized_original_filename}"
     */
    public static function supplierFileName(int $supplierAccountId, string $originalFileName): string
    {
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $sanitized = str_replace(['#', '/', '\\', ' '], '-', preg_replace('#\p{C}+#u', '', $baseName));
        $sanitized = trim($sanitized, '-');
        $unique = uniqid();

        return $extension
            ? "{$supplierAccountId}_{$unique}_{$sanitized}.{$extension}"
            : "{$supplierAccountId}_{$unique}_{$sanitized}";
    }
}
