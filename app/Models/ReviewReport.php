<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: review_reports — an account flags a review for moderation.
 */
class ReviewReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'reported_by_account_id',
        'reported_by_user_id',
        'reason',
        'details',
        'status',
        'reviewed_by_user_id',
        'review_action',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function reportedByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'reported_by_account_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
