<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: quotation_revisions — immutable snapshot of a quotation revision.
 *
 * There is no revision_request_id column; a revision is tied to its request
 * only through the shared quotation_id.
 */
class QuotationRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'revision_no',
        'rfq_version_no',
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
        'change_summary',
        'created_by_account_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'revision_no'     => 'integer',
            'rfq_version_no'  => 'integer',
            'subtotal'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'grand_total'     => 'decimal:2',
            'lead_time_days'  => 'integer',
            'valid_until'     => 'date',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function createdByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationRevisionItem::class, 'quotation_revision_id');
    }

    /**
     * Revision requests raised against the parent quotation. The schema links
     * these through quotation_id only, so this is a has-many-through.
     */
    public function revisionRequests(): HasMany
    {
        return $this->hasMany(QuotationRevisionRequest::class, 'quotation_id', 'quotation_id');
    }
}
