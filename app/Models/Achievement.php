<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: achievements — master badge catalogue, created and managed by
 * admin. Accounts claim these via AccountAchievement, they don't own rows here.
 */
class Achievement extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'badge_icon',
        'type',
        'requirement_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requirement_value' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function accountAchievements(): HasMany
    {
        return $this->hasMany(AccountAchievement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
