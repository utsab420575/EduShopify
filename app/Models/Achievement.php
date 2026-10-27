<?php

namespace App\Models;

use App\Support\BadgeColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: achievements — master badge catalogue, created and managed by
 * admin. Accounts claim these via AccountAchievement, they don't own rows here.
 */
class Achievement extends Model
{
    /**
     * Badge colour used when the admin hasn't picked one (badge_color NULL),
     * by achievement type — Tailwind 600 shades.
     */
    public const DEFAULT_BADGE_COLORS = [
        'milestone' => '#d97706',   // amber-600
        'exhibition' => '#4f46e5',  // indigo-600
        'performance' => '#0284c7', // sky-600
        'default' => '#475569',     // slate-600
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'badge_icon',
        'display_badge',
        'badge_color',
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

    /** Text on the public badge — the admin's short label, else the full name. */
    public function badgeLabel(): string
    {
        return filled($this->display_badge) ? $this->display_badge : $this->name;
    }

    /** Colour in use (#rrggbb): the admin's pick, else automatic by type. */
    public function badgeColor(): string
    {
        if (is_string($this->badge_color) && preg_match(BadgeColor::HEX_PATTERN, $this->badge_color)) {
            return strtolower($this->badge_color);
        }

        return self::DEFAULT_BADGE_COLORS[$this->type] ?? self::DEFAULT_BADGE_COLORS['default'];
    }

    /**
     * Inline style for the public badge (colours only — callers size it).
     * Inline rather than a class so admin and the public frontend, which use
     * separate stylesheets, render the exact same colours.
     */
    public function badgeStyle(): string
    {
        return BadgeColor::style($this->badgeColor());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
