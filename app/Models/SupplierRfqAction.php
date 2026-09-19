<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: supplier_rfq_actions — append-only engagement log for a supplier
 * account against an RFQ (viewed/interested/not_interested/messaged/
 * preparing_quote/quoted). This is history for dashboards/analytics; the
 * live gating state a supplier is in stays on rfq_supplier_queue.status.
 */
class SupplierRfqAction extends Model
{
    protected $fillable = [
        'supplier_account_id',
        'rfq_id',
        'action_type',
        'note',
    ];

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function scopeOfType(Builder $query, string $actionType): Builder
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeForSupplierAccount(Builder $query, int $supplierAccountId): Builder
    {
        return $query->where('supplier_account_id', $supplierAccountId);
    }

    public function scopeForRfq(Builder $query, int $rfqId): Builder
    {
        return $query->where('rfq_id', $rfqId);
    }
}
