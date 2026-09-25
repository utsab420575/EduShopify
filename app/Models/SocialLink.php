<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Table: social_links — one row per platform per socialable entity.
 * socialable is polymorphic (currently only Account, buyer and supplier
 * capabilities alike, since they're both Account rows) so a genuinely
 * different model (e.g. a public Listing/Brand page) could carry its own
 * social links later without a schema change.
 */
class SocialLink extends Model
{
    protected $fillable = [
        'socialable_type',
        'socialable_id',
        'social_platform_id',
        'url',
        'handle',
        'label',
        'is_public',
        'is_verified',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function socialable(): MorphTo
    {
        return $this->morphTo();
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class, 'social_platform_id');
    }

    public function getPlatformIconAttribute(): string
    {
        return $this->platform?->fa_icon ?? 'fa-solid fa-globe';
    }

    public function getPlatformNameAttribute(): string
    {
        return $this->platform?->name ?? ($this->label ?? 'Social Platform');
    }

    public function getPlatformBrandColorAttribute(): string
    {
        return $this->platform?->brand_color_class ?? 'hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/60';
    }

    public function getPlatformBadgeColorAttribute(): string
    {
        return $this->platform?->badge_color_class ?? 'bg-indigo-50 text-indigo-700 border-indigo-200';
    }
}
