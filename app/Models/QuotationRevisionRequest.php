<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: quotation_revision_requests — the buyer asks for a formal revision.
 */
class QuotationRevisionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'requested_by_account_id',
        'requested_by_user_id',
        'requested_changes',
        'requested_fields',
        'response_deadline',
        'status',
        'supplier_response',
        'responded_by_user_id',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_fields'  => 'array',
            'response_deadline' => 'datetime',
            'responded_at'      => 'datetime',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function requestedByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'requested_by_account_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->response_deadline !== null
            && $this->response_deadline->isPast();
    }
}
