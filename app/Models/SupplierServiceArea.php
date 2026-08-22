<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: supplier_service_areas — where a supplier sells, distinct from where it sits.
 */
class SupplierServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'area_level',
        'country_id',
        'state_id',
        'city_id',
        'center_latitude',
        'center_longitude',
        'radius_km',
        'is_primary',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'center_latitude'  => 'decimal:7',
            'center_longitude' => 'decimal:7',
            'radius_km'        => 'decimal:2',
            'is_primary'       => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
