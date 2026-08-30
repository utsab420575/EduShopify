<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReceipt extends Model
{
    use HasFactory;

    protected $table = 'message_receipts';

    protected $fillable = [
        'message_id',
        'user_id',
        'delivered_at',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'seen_at'      => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeSeen(Builder $query): Builder
    {
        return $query->whereNotNull('seen_at');
    }
}
