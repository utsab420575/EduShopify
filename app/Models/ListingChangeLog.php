<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: listing_change_logs — append-only audit of listing edits.
 *
 * The table has created_at but no updated_at, so Eloquent's automatic
 * timestamps are disabled and created_at is stamped on create instead.
 */
class ListingChangeLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'listing_id',
        'changed_by_user_id',
        'changed_fields',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'old_values'     => 'array',
            'new_values'     => 'array',
            'created_at'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->created_at ??= now();
        });
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
