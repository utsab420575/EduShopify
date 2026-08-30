<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: supplier_categories — "what a supplier is capable of supplying,"
 * distinct from listings ("what they currently publish"). Drives
 * open_matching RFQ supplier eligibility; a supplier may be capable of
 * responding to a custom requirement without a currently published listing
 * in that category.
 */
class SupplierCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'category_id',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
