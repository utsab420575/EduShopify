<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: attribute_suggestions — a supplier proposes a new attribute to Admin.
 */
class AttributeSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'suggested_by_user_id',
        'category_id',
        'proposed_name',
        'proposed_input_type',
        'proposed_unit_name',
        'proposed_values',
        'reason',
        'status',
        'resulting_attribute_id',
        'reviewed_by_user_id',
        'review_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_values' => 'array',
            'reviewed_at'     => 'datetime',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function resultingAttribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'resulting_attribute_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
