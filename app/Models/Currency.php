<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: currencies
 */
class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
        'decimal_places',
        'last_rate_update_at',
    ];

    protected $casts = [
        'exchange_rate'       => 'decimal:8',
        'is_default'          => 'boolean',
        'is_active'           => 'boolean',
        'decimal_places'      => 'integer',
        'last_rate_update_at' => 'datetime',
    ];

    public function subscriptionPlans(): HasMany
    {
        return $this->hasMany(SubscriptionPlan::class, 'currency_code', 'code');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
