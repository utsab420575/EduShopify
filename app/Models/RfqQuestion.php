<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: rfq_questions — asked by a supplier ACCOUNT, through a specific user.
 */
class RfqQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'supplier_account_id',
        'asked_by_user_id',
        'question',
        'answer',
        'answered_by_user_id',
        'answered_at',
        'is_public',
        'status',
        'hidden_by_user_id',
        'hidden_at',
        'hidden_reason',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'is_public'   => 'boolean',
            'hidden_at'   => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function askedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asked_by_user_id');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by_user_id');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by_user_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('status', '!=', 'hidden');
    }

    public function isAnswered(): bool
    {
        return $this->status === 'answered';
    }
}
