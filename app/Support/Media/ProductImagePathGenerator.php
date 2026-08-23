<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores Listing (product/service) images day-wise:
 * storage/app/public/product_image/supplier/{d_m_Y}/{filename}
 *
 * The supplier-id filename prefix itself is applied by the caller via
 * addMedia(...)->usingFileName(...) (see ListingController / MediaController),
 * this class only controls the folder.
 */
class ProductImagePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media).'/responsive-images/';
    }

    private function basePath(Media $media): string
    {
        $date = ($media->created_at ?? now())->format('d_m_Y');

        return 'product_image/supplier/'.$date;
    }

    /**
     * "{supplier_account_id}_{sanitized-original-name}" — used with
     * addMedia(...)->usingFileName(...) at every upload call site so the
     * stored filename always identifies which supplier it came from.
     */
    public static function supplierFileName(int $supplierAccountId, string $originalFileName): string
    {
        $sanitized = str_replace(['#', '/', '\\', ' '], '-', preg_replace('#\p{C}+#u', '', $originalFileName));

        return $supplierAccountId.'_'.$sanitized;
    }
}
