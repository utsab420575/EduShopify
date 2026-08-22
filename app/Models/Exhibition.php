<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * Table: exhibitions
 */
class Exhibition extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website',
        'description',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'starts_at'  => 'date',
        'ends_at'    => 'date',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Exhibitors are supplier accounts.
     */
    public function supplierAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            Account::class,
            'exhibition_supplier',
            'exhibition_id',
            'supplier_account_id'
        )->withPivot(['booth_number', 'participation_year'])->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
