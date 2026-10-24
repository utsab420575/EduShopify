<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: certifications — a certificate uploaded by an account. Shown on the
 * account profile once admin approves it.
 */
class Certification extends Model
{
    protected $fillable = [
        'account_id',
        'certification_name',
        'certification_title',
        'certification_description',
        'certification_date',
        'expiry_date',
        'file',
        'status',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'certification_date' => 'date',
            'expiry_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Approved automatically on upload (no admin as approver) — the supplier
     * still owns it and may edit/remove it; admin-approved ones are locked.
     */
    public function isAutoApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_by_user_id === null;
    }

    public function isEditableBySupplier(): bool
    {
        return $this->status === 'pending' || $this->isAutoApproved();
    }

    public function isRemovableBySupplier(): bool
    {
        return in_array($this->status, ['pending', 'rejected'], true) || $this->isAutoApproved();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }
}
