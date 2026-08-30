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
}
