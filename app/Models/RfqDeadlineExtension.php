<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: rfq_deadline_extensions — new_deadline must be later than old_deadline.
 */
class RfqDeadlineExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'extended_by_user_id',
        'deadline_type',
        'old_deadline',
        'new_deadline',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_deadline' => 'datetime',
            'new_deadline' => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function extendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extended_by_user_id');
    }
}
