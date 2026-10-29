<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

/**
 * Readable conversion file names. A product original is stored as
 *   {supplier}_{token}_{name}_original.jpg
 * and its cropped copies become
 *   {supplier}_{token}_{name}_product_banner.webp / _card.webp / _thumb.webp
 * (the "_original" suffix is swapped for the conversion name).
 *
 * Applies to every media item (config media-library.file_namer); names
 * without an "_original" suffix just get "_{conversion}" appended.
 */
class ProductFileNamer extends DefaultFileNamer
{
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $base = preg_replace('/_original$/', '', pathinfo($fileName, PATHINFO_FILENAME));

        return "{$base}_{$conversion->getName()}";
    }
}
