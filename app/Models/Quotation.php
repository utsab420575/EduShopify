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
 * Table: quotations — one live quotation per supplier account per RFQ.
 * Revisions are kept in quotation_revisions.
 */
class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'rfq_id',
        'supplier_account_id',
        'submitted_by_user_id',
        'rfq_version_no',
        'current_revision_no',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_charge',
        'grand_total',
        'currency_code',
        'lead_time_days',
        'valid_until',
        'warranty_terms',
        'support_terms',
        'payment_terms',
        'proposal',
        'status',
        'rejection_comment',
        'rejected_at',
        'buyer_viewed_at',
        'decided_by_user_id',
        'decision_at',
        'submitted_at',
        'revised_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'rfq_version_no'      => 'integer',
            'current_revision_no' => 'integer',
            'subtotal'            => 'decimal:2',
            'tax_amount'          => 'decimal:2',
            'discount_amount'     => 'decimal:2',
            'shipping_charge'     => 'decimal:2',
            'grand_total'         => 'decimal:2',
            'lead_time_days'      => 'integer',
            'valid_until'         => 'date',
            'rejected_at'         => 'datetime',
            'buyer_viewed_at'     => 'datetime',
            'decision_at'         => 'datetime',
            'submitted_at'        => 'datetime',
            'revised_at'          => 'datetime',
            'withdrawn_at'        => 'datetime',
        ];
    }

    /* ── Ownership ──────────────────────────────────────────────────────── */

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    /* ── Contents & history ─────────────────────────────────────────────── */

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function revisionRequests(): HasMany
    {
        return $this->hasMany(QuotationRevisionRequest::class, 'quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class, 'quotation_id');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(QuotationRevision::class, 'quotation_id')->latestOfMany('revision_no');
    }

    public function shortlists(): HasMany
    {
        return $this->hasMany(RfqShortlist::class, 'quotation_id');
    }

    /* ── Outcome ────────────────────────────────────────────────────────── */

    /**
     * awards.quotation_id is unique, so a quotation has at most one award.
     */
    public function award(): HasOne
    {
        return $this->hasOne(Award::class, 'quotation_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'quotation_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'quotation_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'revised', 'shortlisted']);
    }

    public function scopeAwardable(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'revised', 'shortlisted', 'under_review']);
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isAwarded(): bool
    {
        return $this->status === 'awarded';
    }

    public function hasExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }
}
