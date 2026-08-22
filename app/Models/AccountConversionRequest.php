<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: account_conversion_requests — individual → organization, Admin approved.
 */
class AccountConversionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'from_type',
        'to_type',
        'proposed_display_name',
        'proposed_organization_data',
        'submitted_documents',
        'status',
        'submitted_by_user_id',
        'submitted_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_comment',
    ];

    protected function casts(): array
    {
        return [
            'proposed_organization_data' => 'array',
            'submitted_documents'        => 'array',
            'submitted_at'               => 'datetime',
            'reviewed_at'                => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function typeChanges(): HasMany
    {
        return $this->hasMany(AccountTypeChange::class, 'conversion_request_id');
    }
}
