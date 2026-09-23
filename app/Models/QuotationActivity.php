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
        'actor_role',
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function scopeForBuyer(Builder $query): Builder
    {
        return $query->where('actor_role', 'buyer');
    }

    public function scopeForSupplier(Builder $query): Builder
    {
        return $query->where('actor_role', 'supplier');
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where('actor_role', $role);
    }

    public function scopeOfType(Builder $query, string $activityType): Builder
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Human-readable label/icon/badge-color for this row's activity_type —
     * the single source of truth for rendering a timeline entry, shared by
     * the supplier quotation show page's Activity tab and the statistics
     * modal/endpoint so wording never drifts between the two.
     */
    public function label(): string
    {
        return match ($this->activity_type) {
            'drafted' => 'Draft created',
            'submitted' => 'Quotation submitted',
            'unsubmitted' => 'Pulled back to draft',
            'viewed_by_buyer' => 'Viewed by buyer',
            'buyer_messaged' => 'Buyer sent a message',
            'supplier_replied' => 'Supplier replied',
            'buyer_requested_revision' => 'Buyer requested a revision',
            'quotation_updated' => 'Quotation revised',
            'shortlisted' => 'Shortlisted by buyer',
            'awarded' => 'Awarded by buyer',
            'award_cancelled' => 'Award cancelled by buyer',
            'award_declined', 'rejected_by_supplier' => 'Award declined by supplier',
            'accepted' => 'Award accepted by supplier',
            'rejected' => 'Quotation rejected by buyer',
            'expired' => 'Quotation expired',
            default => ucfirst(str_replace('_', ' ', $this->activity_type)),
        };
    }

    public function icon(): string
    {
        return match ($this->activity_type) {
            'drafted' => 'fa-file-pen',
            'submitted' => 'fa-paper-plane',
            'unsubmitted' => 'fa-rotate-left',
            'viewed_by_buyer' => 'fa-eye',
            'buyer_messaged', 'supplier_replied' => 'fa-comment-dots',
            'buyer_requested_revision' => 'fa-rotate',
            'quotation_updated' => 'fa-pen',
            'shortlisted' => 'fa-star',
            'awarded' => 'fa-trophy',
            'award_cancelled' => 'fa-rotate-left',
            'award_declined', 'rejected_by_supplier' => 'fa-circle-xmark',
            'accepted' => 'fa-circle-check',
            'rejected' => 'fa-circle-xmark',
            'expired' => 'fa-hourglass-end',
            default => 'fa-circle',
        };
    }

    public function colorClass(): string
    {
        return match ($this->activity_type) {
            'submitted', 'accepted' => 'bg-emerald-100 text-emerald-700',
            'unsubmitted', 'rejected', 'expired', 'award_cancelled', 'award_declined', 'rejected_by_supplier' => 'bg-red-100 text-red-700',
            'buyer_requested_revision', 'shortlisted' => 'bg-amber-100 text-amber-700',
            'viewed_by_buyer' => 'bg-indigo-100 text-indigo-700',
            'buyer_messaged', 'supplier_replied' => 'bg-blue-100 text-blue-700',
            'awarded' => 'bg-purple-100 text-purple-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
