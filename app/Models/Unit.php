<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: units — global units are shared; supplier_custom units belong to one
 * supplier account.
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'supplier_account_id',
        'name',
        'symbol',
        'unit_type',
        'approval_status',
        'rejection_reason',
        'is_active',
        'created_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Named attributeDefinitions rather than attributes: a relation called
     * "attributes" shadows Eloquent's internal $attributes bag.
     */
    public function attributeDefinitions(): HasMany
    {
        return $this->hasMany(Attribute::class, 'unit_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'unit_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('scope', 'global');
    }

    public function isGlobal(): bool
    {
        return $this->scope === 'global';
    }
}
