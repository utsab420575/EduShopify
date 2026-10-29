<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Table: listings — one shared table for products and services, owned by the
 * supplier ACCOUNT (supplier_account_id), with type-specific detail tables.
 */
class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * gallery       — product images: an uncropped "_original" (≤2000px JPEG)
     *                 plus cropped WebP conversions (product_banner, card, thumb)
     * videos        — short product videos (mp4/webm, ≤2 MB), stored as-is
     * video_posters — a still frame per video (captured in the browser)
     * Sizes/limits: config/product_media.php. Upload flow: ProductMediaService.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('videos')->acceptsMimeTypes(\App\Support\Media\ProductMediaLimits::videoMimeTypes());
        $this->addMediaCollection('video_posters');
    }

    /**
     * Copies of every gallery image (original-image pixel crop boxes in its
     * custom properties). Generated immediately (nonQueued): a database queue
     * needs a worker that shared hosting often doesn't run.
     *
     *  product_banner — "banner_crop": the product page box the supplier picked
     *                   (stage-shaped for cover, full width for contain/fill);
     *                   scaled to 1600 wide (never enlarged). Without one: the
     *                   largest stage-shaped box, centred.
     *  card / thumb   — "crop": the 4:3 card box (centred crop without one).
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $banner = config('product_media.images.banner');
        $bannerCrop = $media ? static::bannerCropFor($media) : null;

        $conversion = $this->addMediaConversion('product_banner')
            ->performOnCollections('gallery')
            ->nonQueued()
            ->format('webp')
            ->quality($banner['quality']);
        if ($bannerCrop) {
            $conversion->manualCrop($bannerCrop['width'], $bannerCrop['height'], $bannerCrop['x'], $bannerCrop['y']);
        }
        $conversion->fit(\Spatie\Image\Enums\Fit::Max, $banner['width'], $banner['max_height']);

        $crop = $media?->getCustomProperty('crop');

        foreach (config('product_media.images.conversions') as $name => [$width, $height, $quality]) {
            $conversion = $this->addMediaConversion($name)
                ->performOnCollections('gallery')
                ->nonQueued()
                ->format('webp')
                ->quality($quality);

            if (is_array($crop) && ($crop['width'] ?? 0) > 0 && ($crop['height'] ?? 0) > 0) {
                $conversion->manualCrop((int) $crop['width'], (int) $crop['height'], (int) $crop['x'], (int) $crop['y']);
            }

            $conversion->fit(\Spatie\Image\Enums\Fit::Crop, $width, $height);
        }
    }

    /**
     * The product-banner crop box for an image: the chosen one, or (older
     * images) the largest stage-shaped box, centred.
     *
     * @return array{x:int,y:int,width:int,height:int}|null
     */
    public static function bannerCropFor(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): ?array
    {
        $crop = $media->getCustomProperty('banner_crop');
        if (is_array($crop) && ($crop['width'] ?? 0) > 0 && ($crop['height'] ?? 0) > 0) {
            return array_map('intval', array_intersect_key($crop, array_flip(['x', 'y', 'width', 'height'])));
        }

        [$width, $height] = static::mediaDimensions($media);
        if (! $width || ! $height) {
            return null;
        }
        $ratio = (float) config('product_media.images.banner.default_ratio');
        $cropWidth = (int) min($width, round($height * $ratio));
        $cropHeight = (int) min($height, round($cropWidth / $ratio));

        return ['x' => intdiv($width - $cropWidth, 2), 'y' => intdiv($height - $cropHeight, 2), 'width' => $cropWidth, 'height' => $cropHeight];
    }

    /** Stored original's pixel size (custom properties, else read from the file). */
    public static function mediaDimensions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        $width = (int) $media->getCustomProperty('width', 0);
        $height = (int) $media->getCustomProperty('height', 0);
        if ((! $width || ! $height) && is_file($path = $media->getPath()) && ($size = @getimagesize($path))) {
            [$width, $height] = $size;
        }

        return [(int) $width, (int) $height];
    }

    /** How a gallery image fills the product page stage: cover (default), contain or fill. */
    public static function bannerFitFor(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        $fit = $media?->getCustomProperty('banner_fit');

        return in_array($fit, config('product_media.images.fits'), true) ? $fit : 'cover';
    }

    /** A gallery image's URL in a given size, falling back to the original until it exists. */
    public static function mediaUrl(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media, string $conversion): ?string
    {
        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }

    /** The cover image: the chosen primary, else the first gallery image. */
    public function coverMedia(): ?\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        $primary = $this->primaryImage;

        return $primary && $primary->collection_name === 'gallery' ? $primary : $this->getFirstMedia('gallery');
    }

    /** Card-size cover image URL (product cards/listings). */
    public function cardImageUrl(): ?string
    {
        return static::mediaUrl($this->coverMedia(), 'card');
    }

    protected static function booted(): void
    {
        static::saving(function (Listing $listing) {
            if (! $listing->listing_type_id) {
                $listing->listing_type_id = ListingType::firstOrCreate(
                    ['code' => 'product'],
                    ['name' => 'Product', 'is_active' => true]
                )->id;
            }
            if (! $listing->pricing_type_id) {
                $listing->pricing_type_id = PricingType::firstOrCreate(
                    ['code' => 'fixed'],
                    ['name' => 'Fixed Catalog Price', 'is_active' => true]
                )->id;
            }
            if (! $listing->sales_mode_id) {
                $listing->sales_mode_id = SalesMode::firstOrCreate(
                    ['code' => 'both'],
                    ['name' => 'Both', 'is_active' => true]
                )->id;
            }
        });
    }

    public function fill(array $attributes)
    {
        if (isset($attributes['listing_type']) && ! isset($attributes['listing_type_id'])) {
            $val = $attributes['listing_type'];
            $attributes['listing_type_id'] = is_numeric($val)
                ? (int)$val
                : ListingType::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst($val), 'is_active' => true]
                )->id;
            unset($attributes['listing_type']);
        }

        if (isset($attributes['pricing_type']) && ! isset($attributes['pricing_type_id'])) {
            $val = $attributes['pricing_type'];
            $attributes['pricing_type_id'] = is_numeric($val)
                ? (int)$val
                : PricingType::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst(str_replace('_', ' ', $val)), 'is_active' => true]
                )->id;
            unset($attributes['pricing_type']);
        }

        if (isset($attributes['sales_mode']) && ! isset($attributes['sales_mode_id'])) {
            $val = $attributes['sales_mode'];
            $attributes['sales_mode_id'] = is_numeric($val)
                ? (int)$val
                : SalesMode::firstOrCreate(
                    ['code' => $val],
                    ['name' => ucfirst(str_replace('_', ' ', $val)), 'is_active' => true]
                )->id;
            unset($attributes['sales_mode']);
        }

        return parent::fill($attributes);
    }

    protected $fillable = [
        'supplier_account_id',
        'created_by_user_id',
        'listing_type_id',
        'listing_number',
        'main_category_id',
        'brand_id',
        'primary_image_media_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'pricing_type_id',
        'sales_mode_id',
        'base_price',
        'compare_at_price',
        'currency_code',
        'min_order_quantity',
        'unit_id',
        'extra_specs',
        'approval_status',
        'setup_step',
        'setup_completed_at',
        'last_autosaved_at',
        'rejection_reason',
        'approved_by_user_id',
        'approved_at',
        'is_active',
        'is_featured',
        'published_at',
        'product_rating',
        'product_reviews_count',
    ];

    protected function casts(): array
    {
        return [
            'base_price'             => 'decimal:2',
            'compare_at_price'       => 'decimal:2',
            'min_order_quantity'     => 'decimal:3',
            'extra_specs'            => 'array',
            'setup_step'             => 'integer',
            'setup_completed_at'     => 'datetime',
            'last_autosaved_at'      => 'datetime',
            'approved_at'            => 'datetime',
            'is_active'              => 'boolean',
            'is_featured'            => 'boolean',
            'published_at'           => 'datetime',
            'product_rating'         => 'decimal:2',
            'product_reviews_count'  => 'integer',
        ];
    }

    public function primaryImage(): BelongsTo
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'primary_image_media_id');
    }

    /* ── Ownership ──────────────────────────────────────────────────────── */

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /* ── Classification ─────────────────────────────────────────────────── */

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /* ── Listing classification lookup relations ─────────────────────────── */

    public function listingType(): BelongsTo
    {
        return $this->belongsTo(ListingType::class, 'listing_type_id');
    }

    public function pricingType(): BelongsTo
    {
        return $this->belongsTo(PricingType::class, 'pricing_type_id');
    }

    public function salesMode(): BelongsTo
    {
        return $this->belongsTo(SalesMode::class, 'sales_mode_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'listing_categories', 'listing_id', 'category_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function listingCategories(): HasMany
    {
        return $this->hasMany(ListingCategory::class, 'listing_id');
    }

    /* ── Type-specific details ──────────────────────────────────────────── */

    public function productDetail(): HasOne
    {
        return $this->hasOne(ProductDetail::class, 'listing_id');
    }

    public function serviceDetail(): HasOne
    {
        return $this->hasOne(ServiceDetail::class, 'listing_id');
    }

    /* ── Specification & pricing ────────────────────────────────────────── */

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ListingAttributeValue::class, 'listing_id');
    }

    /**
     * Product-level reviews (review_type=product) — distinct from
     * supplierAccount's own reviews, which rate the supplier account, not
     * this specific listing. See Review::scopeProduct().
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'listing_id')->product();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ListingVariant::class, 'listing_id');
    }

    public function allTierPrices(): HasMany
    {
        return $this->hasMany(ListingTierPrice::class, 'listing_id');
    }

    public function globalTierPrices(): HasMany
    {
        return $this->hasMany(ListingTierPrice::class, 'listing_id')->whereNull('listing_variant_id');
    }

    public function tierPrices(): HasMany
    {
        return $this->globalTierPrices();
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ListingChangeLog::class, 'listing_id');
    }

    /* ── Downstream references ──────────────────────────────────────────── */

    public function sourcedRfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'source_listing_id');
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'listing_id');
    }

    public function offeredQuotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'offered_listing_id');
    }

    public function contactInquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class, 'listing_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('published_at');
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('code', 'product'));
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('code', 'service'));
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isProduct(): bool
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? ($type->code === 'product') : ($type === 'product');
    }

    public function isService(): bool
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? ($type->code === 'service') : ($type === 'service');
    }

    /**
     * Backward-compatible code accessors so existing code like
     * `$listing->listing_type`, `$listing->pricing_type`, `$listing->sales_mode`
     * continues to return the string code after the enum→FK migration.
     */
    public function getListingTypeAttribute(): ?string
    {
        $type = $this->getRelationValue('listingType');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function getPricingTypeAttribute(): ?string
    {
        $type = $this->getRelationValue('pricingType');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function getSalesModeAttribute(): ?string
    {
        $type = $this->getRelationValue('salesMode');
        return is_object($type) ? $type->code : (is_string($type) ? $type : null);
    }

    public function setListingTypeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['listing_type_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['listing_type_id'] = ListingType::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst($value), 'is_active' => true]
            )->id;
        }
    }

    public function setPricingTypeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['pricing_type_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['pricing_type_id'] = PricingType::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst(str_replace('_', ' ', $value)), 'is_active' => true]
            )->id;
        }
    }

    public function setSalesModeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['sales_mode_id'] = (int)$value;
        } elseif (is_string($value)) {
            $this->attributes['sales_mode_id'] = SalesMode::firstOrCreate(
                ['code' => $value],
                ['name' => ucfirst(str_replace('_', ' ', $value)), 'is_active' => true]
            )->id;
        }
    }

    /**
     * "Summary Description of Product" (short_description) as safe HTML for
     * display: editor HTML is re-sanitized (App\Support\RichText); older
     * plain-text summaries are escaped with their line breaks kept.
     */
    public function summaryHtml(): string
    {
        $value = (string) $this->short_description;

        return \App\Support\RichText::isHtml($value)
            ? \App\Support\RichText::sanitize($value)
            : nl2br(e(trim($value)));
    }

    /** The summary as plain text — for snippets, meta tags, RFQ/quotation item text. */
    public function summaryText(): string
    {
        return \App\Support\RichText::toText((string) $this->short_description);
    }

    public function isPublished(): bool
    {
        return $this->approval_status === 'approved'
            && $this->is_active
            && $this->published_at !== null;
    }
}
