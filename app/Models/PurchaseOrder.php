<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: purchase_orders — issued after an award is accepted. Phase 1 collects
 * no payment through the platform; payment_status is informational only.
 *
 * There is no delivery location column on this table.
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'award_id',
        'rfq_id',
        'quotation_id',
        'buyer_account_id',
        'supplier_account_id',
        'created_by_user_id',
        'subtotal',
        'tax_amount',
        'shipping_charge',
        'discount_amount',
        'grand_total',
        'currency_code',
        'status',
        'payment_method_note',
        'payment_status',
        'issued_at',
        'confirmed_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'grand_total'     => 'decimal:2',
            'issued_at'       => 'datetime',
            'confirmed_at'    => 'datetime',
            'delivered_at'    => 'datetime',
            'completed_at'    => 'datetime',
            'cancelled_at'    => 'datetime',
        ];
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class, 'award_id');
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PurchaseOrderStatusHistory::class, 'purchase_order_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'purchase_order_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
