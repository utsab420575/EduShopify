<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: reviews — written by a buyer ACCOUNT, rating either a supplier
 * ACCOUNT (review_type=supplier, listing_id NULL) or one specific
 * listing/product (review_type=product, listing_id set). review_context
 * (quotation_experience / purchase_experience) is orthogonal to that — it's
 * which order the review came from, not what it's rating.
 */
class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'buyer_account_id',
        'supplier_account_id',
        'listing_id',
        'review_type',
        'created_by_user_id',
        'review_context',
        'rfq_id',
        'quotation_id',
        'purchase_order_id',
        'rating',
        'title',
        'comment',
        'status',
        'moderated_by_user_id',
        'moderation_reason',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'rating'       => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_user_id');
    }

    /**
     * unique(review_id, supplier_account_id) allows one public supplier reply.
     */
    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class, 'review_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class, 'review_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class, 'review_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePurchaseExperience(Builder $query): Builder
    {
        return $query->where('review_context', 'purchase_experience');
    }

    public function scopeQuotationExperience(Builder $query): Builder
    {
        return $query->where('review_context', 'quotation_experience');
    }

    public function scopeSupplier(Builder $query): Builder
    {
        return $query->where('review_type', 'supplier');
    }

    public function scopeProduct(Builder $query): Builder
    {
        return $query->where('review_type', 'product');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
