<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: purchase_order_status_history (singular) — append-only status trail.
 *
 * The table has created_at but no updated_at, so Eloquent's automatic
 * timestamps are disabled and created_at is stamped on create instead.
 */
class PurchaseOrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_status_history';

    public $timestamps = false;

    protected $fillable = [
        'purchase_order_id',
        'old_status',
        'new_status',
        'changed_by_account_id',
        'changed_by_user_id',
        'comment',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $history) {
            $history->created_at ??= now();
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function changedByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'changed_by_account_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
