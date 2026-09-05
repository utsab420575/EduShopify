<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: countries
 */
class Country extends Model
{
    protected $fillable = [
        'name',
        'iso2',
        'iso3',
        'phone_code',
        'currency_code',
        'flag',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Derived from iso2 via Unicode regional indicator symbols, rather than
     * trusting the `flag` column's format (unused elsewhere in the app).
     */
    public function getFlagEmojiAttribute(): ?string
    {
        if (! $this->iso2 || strlen($this->iso2) !== 2) {
            return null;
        }

        $code = strtoupper($this->iso2);

        return mb_chr(0x1F1E6 + (ord($code[0]) - 65)).mb_chr(0x1F1E6 + (ord($code[1]) - 65));
    }
}
