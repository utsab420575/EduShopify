<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Table: saved_items — bookmarks, keyed by an item_type enum plus item_id
 * rather than a conventional *_type class column.
 */
class SavedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'saved_by_user_id',
        'visibility',
        'item_type',
        'item_id',
        'notes',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function savedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }

    /**
     * The bookmarked record. item_type holds a short alias ('listing',
     * 'supplier', 'rfq', 'quotation') resolved through the morph map
     * registered in AppServiceProvider.
     *
     * Because item_type is a database enum, set it explicitly when saving
     * rather than relying on associate().
     */
    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    public function scopeOfType(Builder $query, string $itemType): Builder
    {
        return $query->where('item_type', $itemType);
    }

    public function scopeSharedWithAccount(Builder $query): Builder
    {
        return $query->where('visibility', 'account');
    }
}
