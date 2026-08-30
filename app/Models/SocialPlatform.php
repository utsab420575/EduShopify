<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: social_platforms — small master list of platforms a socialable
 * entity (currently just Account) can link to (Facebook, LinkedIn, etc).
 */
class SocialPlatform extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'base_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
