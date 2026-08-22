<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: brands — global brands are shared; supplier-owned brands belong to one
 * supplier account and require Admin approval.
 */
class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_type',
        'supplier_account_id',
        'name',
        'slug',
        'logo',
        'website',
        'description',
        'approval_status',
        'rejection_reason',
        'reviewed_by_user_id',
        'reviewed_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'brand_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('owner_type', 'global');
    }

    public function isGlobal(): bool
    {
        return $this->owner_type === 'global';
    }
}
