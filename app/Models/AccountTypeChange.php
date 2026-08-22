<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_type_changes — immutable record of an approved conversion.
 */
class AccountTypeChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'conversion_request_id',
        'old_type',
        'new_type',
        'old_display_name',
        'new_display_name',
        'old_snapshot',
        'new_snapshot',
        'changed_by_user_id',
        'approved_by_user_id',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'old_snapshot' => 'array',
            'new_snapshot' => 'array',
            'changed_at'   => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function conversionRequest(): BelongsTo
    {
        return $this->belongsTo(AccountConversionRequest::class, 'conversion_request_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
