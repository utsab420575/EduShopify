<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Table: awards — one winner per RFQ in Phase 1, but several attempts allowed
 * when a supplier rejects. unique(rfq_id, award_attempt_no) and unique(quotation_id).
 */
class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'award_number',
        'rfq_id',
        'quotation_id',
        'buyer_account_id',
        'supplier_account_id',
        'awarded_by_user_id',
        'award_attempt_no',
        'status',
        'award_note',
        'supplier_response_note',
        'supplier_rejection_reason',
        'response_deadline',
        'awarded_at',
        'responded_at',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'award_attempt_no'  => 'integer',
            'response_deadline' => 'datetime',
            'awarded_at'        => 'datetime',
            'responded_at'      => 'datetime',
            'accepted_at'       => 'datetime',
            'rejected_at'       => 'datetime',
            'cancelled_at'      => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by_user_id');
    }

    /**
     * purchase_orders.award_id is unique, so at most one PO per award.
     */
    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'award_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeAwaitingResponse(Builder $query): Builder
    {
        return $query->where('status', 'pending_supplier_response');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isAwaitingResponse(): bool
    {
        return $this->status === 'pending_supplier_response';
    }

    public function responseOverdue(): bool
    {
        return $this->isAwaitingResponse()
            && $this->response_deadline !== null
            && $this->response_deadline->isPast();
    }
}
