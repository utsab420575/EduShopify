<?php

namespace App\Services\Catalog;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Product (listing) images and videos — every upload path goes through here.
 *
 * Images: the upload (already shrunk/cropped in the browser) is stored as an
 * uncropped "_original" JPEG (longest side ≤ config product_media.images.
 * original_max_side), with the supplier's crop box in its custom properties.
 * Listing::registerMediaConversions() then cuts the WebP copies
 * (product_banner from the "banner_crop" box, card / thumb from the 4:3
 * "crop" box; "banner_fit" = how the banner fills the product page). Re-cropping only rewrites
 * the box and regenerates the copies — the original is never touched.
 *
 * Videos: stored as uploaded (mp4/webm, size-limited), plus an optional
 * poster frame captured in the browser.
 *
 * File names are "{supplier}_{random}_{name}_{role}.{ext}" — the random token
 * keeps two uploads of the same file name from ever sharing one file.
 */
class ProductMediaService
{
    /**
     * @param  array|null  $crop  4:3 card box, in uploaded-image pixels
     * @param  array|null  $bannerCrop  product page box (cover: stage-shaped; contain/fill: full width), uploaded-image pixels
     * @param  string|null  $fit  how the banner fills the product page stage: cover|contain|fill
     */
    public function storeImage(Listing $listing, int $supplierAccountId, UploadedFile $file, ?array $crop = null, ?array $bannerCrop = null, ?string $fit = null): Media
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath()); // EXIF rotation applied on read

        $sourceWidth = $image->width();
        $maxSide = (int) config('product_media.images.original_max_side', 2000);
        $image->scaleDown(width: $maxSide, height: $maxSide);
        $scale = $image->width() / max(1, $sourceWidth);

        // JPEG has no transparency — put transparent PNG/WebP areas on white.
        $image->blendTransparency('ffffff');

        $tmp = tempnam(sys_get_temp_dir(), 'pimg');
        file_put_contents($tmp, (string) $image->toJpeg((int) config('product_media.images.original_quality', 85)));

        $name = $this->baseName($file->getClientOriginalName(), 'image');

        return $listing->addMedia($tmp)
            ->usingName($name)
            ->usingFileName($this->fileName($supplierAccountId, $name, 'original', 'jpg'))
            ->withCustomProperties([
                'width' => $image->width(),
                'height' => $image->height(),
                'crop' => $this->clampCrop($crop, $image->width(), $image->height(), $scale),
                'banner_crop' => $this->clampBannerCrop($bannerCrop, $image->width(), $image->height(), $scale),
                'banner_fit' => $this->fit($fit),
            ])
            ->toMediaCollection('gallery');
    }

    /**
     * New crop boxes / fit (original-image pixels) → regenerate the copies.
     * Any part left null keeps its current value.
     */
    public function recrop(Media $media, ?array $crop, ?array $bannerCrop = null, ?string $fit = null): Media
    {
        [$width, $height] = Listing::mediaDimensions($media);
        $media->setCustomProperty('width', $width)->setCustomProperty('height', $height);

        if ($crop) {
            $media->setCustomProperty('crop', $this->clampCrop($crop, $width, $height));
        }
        if ($bannerCrop) {
            $media->setCustomProperty('banner_crop', $this->clampBannerCrop($bannerCrop, $width, $height));
        }
        if ($fit !== null) {
            $media->setCustomProperty('banner_fit', $this->fit($fit));
        }
        $media->save();
        $media->touch(); // new updated_at → cache-busting ?v= on every URL

        app(FileManipulator::class)->createDerivedFiles($media);

        return $media->refresh();
    }

    public function storeVideo(Listing $listing, int $supplierAccountId, UploadedFile $file, ?UploadedFile $poster = null): Media
    {
        $name = $this->baseName($file->getClientOriginalName(), 'video');
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'mp4';

        $video = $listing->addMedia($file)
            ->usingName($name)
            ->usingFileName($this->fileName($supplierAccountId, $name, 'video', $extension))
            ->toMediaCollection('videos');

        if ($poster) {
            $posterMedia = $listing->addMedia($poster)
                ->usingName($name.' poster')
                ->usingFileName($this->fileName($supplierAccountId, $name, 'video_poster', 'jpg'))
                ->withCustomProperties(['video_id' => $video->id])
                ->toMediaCollection('video_posters');

            $video->setCustomProperty('poster_id', $posterMedia->id)->save();
        }

        return $video;
    }

    /** Delete an image or video (its poster too); a deleted cover falls back to the next image. */
    public function delete(Listing $listing, Media $media): void
    {
        if ($media->collection_name === 'videos' && ($posterId = $media->getCustomProperty('poster_id'))) {
            $listing->media()->whereKey($posterId)->first()?->delete();
        }

        $wasCover = (int) $listing->primary_image_media_id === (int) $media->id;
        $media->delete();

        if ($wasCover) {
            $listing->update(['primary_image_media_id' => $listing->fresh()->getFirstMedia('gallery')?->id]);
        }
    }

    /**
     * Wizard payload: gallery images (with every size + crop data) and videos.
     *
     * @return array{media: list<array>, videos: list<array>}
     */
    public function payload(Listing $listing): array
    {
        $listing->loadMissing('media');
        $all = $listing->media;
        $posters = $all->where('collection_name', 'video_posters')->keyBy('id');

        return [
            'media' => $all->where('collection_name', 'gallery')->sortBy('order_column')->values()->map(fn (Media $m) => [
                'id' => $m->id,
                'url' => $this->versioned($m, 'card'),
                'thumb_url' => $this->versioned($m, 'thumb'),
                'banner_url' => $this->versioned($m, 'product_banner'),
                'original_url' => $this->versioned($m, ''),
                'file_name' => $m->name ?: $m->file_name,
                'size' => $m->human_readable_size,
                'is_primary' => (int) $m->id === (int) $listing->primary_image_media_id,
                'crop' => $m->getCustomProperty('crop'),
                'banner_crop' => Listing::bannerCropFor($m),
                'banner_fit' => Listing::bannerFitFor($m),
            ])->all(),
            'videos' => $all->where('collection_name', 'videos')->sortBy('order_column')->values()->map(fn (Media $m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'poster_url' => ($poster = $posters->get($m->getCustomProperty('poster_id'))) ? $poster->getUrl() : null,
                'file_name' => $m->name ?: $m->file_name,
                'size' => $m->human_readable_size,
            ])->all(),
        ];
    }

    /** URL of a size ('' = original) with ?v= so a re-crop is never served from cache. */
    public function versioned(Media $media, string $conversion): string
    {
        $url = $conversion !== '' ? Listing::mediaUrl($media, $conversion) : $media->getUrl();

        return $url.'?v='.($media->updated_at?->timestamp ?? 0);
    }

    /**
     * Crop box clamped inside the image, in stored-original pixels. $scale
     * converts from the uploaded image (before the server downscale).
     */
    private function clampCrop(?array $crop, int $width, int $height, float $scale = 1.0): ?array
    {
        if (! $crop || $width < 1 || $height < 1 || ! isset($crop['width'], $crop['height'])) {
            return null;
        }

        $x = max(0, min((int) round(($crop['x'] ?? 0) * $scale), $width - 1));
        $y = max(0, min((int) round(($crop['y'] ?? 0) * $scale), $height - 1));
        $w = max(1, min((int) round($crop['width'] * $scale), $width - $x));
        $h = max(1, min((int) round($crop['height'] * $scale), $height - $y));

        return ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h];
    }

    /**
     * Banner box: as chosen (cover: stage-shaped, anywhere in the photo);
     * without x/width it's the full image width (contain/fill).
     */
    private function clampBannerCrop(?array $crop, int $width, int $height, float $scale = 1.0): ?array
    {
        if (! $crop || $width < 1 || $height < 1 || ! isset($crop['height'])) {
            return null;
        }
        if (! isset($crop['width'], $crop['x'])) {
            $crop = ['x' => 0, 'width' => $width / $scale] + $crop;
        }

        return $this->clampCrop($crop, $width, $height, $scale);
    }

    private function fit(?string $fit): string
    {
        return in_array($fit, config('product_media.images.fits'), true) ? $fit : 'cover';
    }

    private function baseName(string $clientName, string $fallback): string
    {
        $slug = Str::slug(pathinfo($clientName, PATHINFO_FILENAME), '_');

        return Str::limit($slug !== '' ? $slug : $fallback, 40, '');
    }

    private function fileName(int $supplierAccountId, string $name, string $role, string $extension): string
    {
        return "{$supplierAccountId}_".Str::lower(Str::random(6))."_{$name}_{$role}.{$extension}";
    }
}
