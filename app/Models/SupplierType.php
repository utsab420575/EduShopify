<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * Table: supplier_types — global catalogue of supplier kinds.
 */
class SupplierType extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Suppliers are accounts, joined through the supplier_account_id pivot key.
     */
    public function supplierAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            Account::class,
            'supplier_supplier_type',
            'supplier_type_id',
            'supplier_account_id'
        )->withPivot('is_primary')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
