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

    public function getFaIconAttribute(): string
    {
        if (! empty($this->icon)) {
            return $this->icon;
        }

        return match (strtolower($this->slug ?? '')) {
            'facebook' => 'fa-brands fa-facebook-f',
            'linkedin' => 'fa-brands fa-linkedin-in',
            'instagram' => 'fa-brands fa-instagram',
            'youtube' => 'fa-brands fa-youtube',
            'x', 'twitter' => 'fa-brands fa-x-twitter',
            'tiktok' => 'fa-brands fa-tiktok',
            'whatsapp' => 'fa-brands fa-whatsapp',
            'github' => 'fa-brands fa-github',
            'pinterest' => 'fa-brands fa-pinterest-p',
            default => 'fa-solid fa-globe',
        };
    }

    public function getBrandColorClassAttribute(): string
    {
        return match (strtolower($this->slug ?? '')) {
            'facebook' => 'hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50/60',
            'linkedin' => 'hover:text-sky-600 hover:border-sky-300 hover:bg-sky-50/60',
            'instagram' => 'hover:text-pink-600 hover:border-pink-300 hover:bg-pink-50/60',
            'youtube' => 'hover:text-red-600 hover:border-red-300 hover:bg-red-50/60',
            'x', 'twitter' => 'hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100',
            'tiktok' => 'hover:text-black hover:border-gray-500 hover:bg-gray-100',
            'whatsapp' => 'hover:text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50/60',
            default => 'hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/60',
        };
    }

    public function getBadgeColorClassAttribute(): string
    {
        return match (strtolower($this->slug ?? '')) {
            'facebook' => 'bg-blue-50 text-blue-700 border-blue-200',
            'linkedin' => 'bg-sky-50 text-sky-700 border-sky-200',
            'instagram' => 'bg-pink-50 text-pink-700 border-pink-200',
            'youtube' => 'bg-red-50 text-red-700 border-red-200',
            'x', 'twitter' => 'bg-gray-100 text-gray-800 border-gray-300',
            'tiktok' => 'bg-neutral-100 text-neutral-800 border-neutral-300',
            'whatsapp' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }
}
