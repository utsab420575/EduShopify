<?php

/*
|--------------------------------------------------------------------------
| Product (listing) images & videos
|--------------------------------------------------------------------------
|
| One place for every limit/size used by the product wizard, the media
| service (App\Services\Catalog\ProductMediaService) and the Listing media
| conversions — so they can later move to a System Settings table without
| touching code.
|
*/

return [

    'images' => [
        // Upload limit per image (KB). The browser shrinks + crops before upload,
        // so real uploads are usually well under this.
        'max_upload_kb' => 10240,
        'max_per_listing' => 15,

        // Card/thumbnail crop shape (width / height) — custom property "crop".
        'crop_ratio' => [4, 3],

        // The stored "original": uncropped, longest side capped, re-encoded JPEG.
        // Kept so the supplier can re-crop later without re-uploading.
        'original_max_side' => 2000,
        'original_quality' => 85,

        // Product page banner — custom property "banner_crop". With "cover" the
        // box has the stage's shape (what's selected is exactly what desktop
        // shows); with "contain"/"fill" it's the full image width and the
        // supplier picks the height. Scaled to this width (never enlarged,
        // 1600 = sharp on 2x screens for the ~845px stage); capped at max_height.
        'banner' => [
            'width' => 1600,
            'max_height' => 1600,
            'quality' => 80,
            // The stage's shape (width / height): cover crop box + default box.
            'default_ratio' => 845 / 400,
            // Default fit for a new photo: cover when its shape is within this
            // factor of the stage's shape, otherwise contain (nothing cut off).
            'cover_tolerance' => 1.2,
        ],

        // How each photo fills the product page stage (custom property "banner_fit").
        'fits' => ['cover', 'contain', 'fill'],

        // The product page stage the banner is shown in (left column, beside the sidebar).
        'stage' => [
            'desktop' => ['width' => 845, 'height' => 400],
            'mobile' => ['width' => 375, 'height' => 260],
        ],

        // 4:3 copies (WebP) cut from the card crop box. name => [width, height, quality]
        'conversions' => [
            'card' => [600, 450, 75],    // product cards / listings
            'thumb' => [160, 120, 70],   // gallery thumbnails
        ],
    ],

    'videos' => [
        'max_upload_kb' => 2048, // 2 MB per video
        'max_per_listing' => 5,
        'mimetypes' => ['video/mp4', 'video/webm'],
        'extensions' => ['mp4', 'webm'],
        'poster_max_kb' => 1024,
    ],

];
