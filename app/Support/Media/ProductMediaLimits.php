<?php

namespace App\Support\Media;

use App\Models\Setting;

/**
 * Product media limits — photos/videos per product, video size and file
 * types — set by the admin in System Settings (group "product_media") with
 * config/product_media.php as the fallback. Read by the product wizard
 * (browser checks), its validation and the media collection.
 */
class ProductMediaLimits
{
    /** Video extension → MIME types the file's content may report. */
    private const VIDEO_MIME_TYPES = [
        'mp4' => ['video/mp4', 'application/mp4'],
        'm4v' => ['video/x-m4v', 'video/mp4'],
        'webm' => ['video/webm'],
        'mov' => ['video/quicktime'],
        'ogv' => ['video/ogg'],
        'ogg' => ['video/ogg'],
        'avi' => ['video/x-msvideo', 'video/avi'],
        'mkv' => ['video/x-matroska'],
        '3gp' => ['video/3gpp'],
    ];

    /** Extensions the admin may allow (anything else isn't a video we can play). */
    public static function knownVideoExtensions(): array
    {
        return array_keys(self::VIDEO_MIME_TYPES);
    }

    /** Most photos one product can have. */
    public static function maxImages(): int
    {
        return max(1, (int) Setting::get('product_media', 'max_images', config('product_media.images.max_per_listing')));
    }

    /** Most videos one product can have (0 = videos turned off). */
    public static function maxVideos(): int
    {
        return max(0, (int) Setting::get('product_media', 'max_videos', config('product_media.videos.max_per_listing')));
    }

    public static function videoMaxKb(): int
    {
        return max(1, (int) Setting::get('product_media', 'video_max_kb', config('product_media.videos.max_upload_kb')));
    }

    /** @return list<string> lower-case, no dots, e.g. ['mp4', 'webm'] */
    public static function videoExtensions(): array
    {
        $value = Setting::get('product_media', 'video_extensions', config('product_media.videos.extensions'));
        $list = is_array($value) ? $value : explode(',', (string) $value);
        $list = array_values(array_unique(array_filter(array_map(fn ($e) => strtolower(trim((string) $e, " .\t\n")), $list))));

        return $list ?: config('product_media.videos.extensions');
    }

    /** @return list<string> */
    public static function videoMimeTypes(): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn ($ext) => self::VIDEO_MIME_TYPES[$ext] ?? ["video/{$ext}"],
            static::videoExtensions(),
        ))));
    }

    /** "MP4, WebM"-style label for messages. */
    public static function videoExtensionsLabel(): string
    {
        return implode(', ', array_map(fn ($e) => $e === 'webm' ? 'WebM' : strtoupper($e), static::videoExtensions()));
    }

    public static function videoMaxLabel(): string
    {
        $kb = static::videoMaxKb();

        return $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.').' MB' : $kb.' KB';
    }
}
