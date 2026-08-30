<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ConversationContext extends Model
{
    use HasFactory;

    protected $table = 'conversation_contexts';

    protected $fillable = [
        'conversation_id',
        'context_type',
        'context_id',
        'added_by_user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /**
     * Resolves the polymorphic business model (Listing, Rfq, Quotation, PurchaseOrder).
     * For general/support contexts, resolves safely to null.
     */
    public function context(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'context_type', 'context_id');
    }
}
