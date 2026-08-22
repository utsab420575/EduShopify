<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: capability_application_history (singular table name — immutable history).
 */
class CapabilityApplicationHistory extends Model
{
    use HasFactory;

    protected $table = 'capability_application_history';

    protected $fillable = [
        'account_capability_id',
        'attempt_no',
        'submitted_snapshot',
        'status',
        'reviewed_by_user_id',
        'review_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_no'         => 'integer',
            'submitted_snapshot' => 'array',
            'reviewed_at'        => 'datetime',
        ];
    }

    public function accountCapability(): BelongsTo
    {
        return $this->belongsTo(AccountCapability::class, 'account_capability_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
