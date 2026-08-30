<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: attribute_custom_value_reviews — Tracks supplier-entered "Other" custom values
 * on attributes for admin moderation and promoting to master attribute_values.
 */
class AttributeCustomValueReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'custom_value',
        'supplier_account_id',
        'first_listing_id',
        'submitted_by_user_id',
        'usage_count',
        'status',
        'resulting_attribute_value_id',
        'reviewed_by_user_id',
        'review_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function firstListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'first_listing_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function resultingAttributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'resulting_attribute_value_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
