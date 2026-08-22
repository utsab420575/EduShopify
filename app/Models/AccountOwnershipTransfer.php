<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_ownership_transfers
 */
class AccountOwnershipTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'from_user_id',
        'to_user_id',
        'requested_by_user_id',
        'status',
        'accepted_at',
        'completed_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
