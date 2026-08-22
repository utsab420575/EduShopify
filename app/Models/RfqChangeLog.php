<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: rfq_change_logs — one row per post-publication RFQ version bump.
 */
class RfqChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'from_version_no',
        'to_version_no',
        'change_level',
        'changed_fields',
        'old_snapshot',
        'new_snapshot',
        'requires_quotation_revision',
        'changed_by_user_id',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_version_no'             => 'integer',
            'to_version_no'               => 'integer',
            'changed_fields'              => 'array',
            'old_snapshot'                => 'array',
            'new_snapshot'                => 'array',
            'requires_quotation_revision' => 'boolean',
            'changed_at'                  => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function isMajor(): bool
    {
        return $this->change_level === 'major';
    }
}
