<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: quotation_activities — append-only timeline of a quotation's
 * lifecycle/communication history. quotations.status still holds the
 * CURRENT state; this table only ever gets new rows, never updates.
 */
class QuotationActivity extends Model
{
    protected $fillable = [
        'quotation_id',
        'rfq_id',
        'supplier_account_id',
        'buyer_account_id',
        'activity_type',
        'message',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function scopeOfType(Builder $query, string $activityType): Builder
    {
        return $query->where('activity_type', $activityType);
    }
}
