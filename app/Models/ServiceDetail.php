<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: service_details — service-only fields for a listing.
 */
class ServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'service_mode',
        'duration_value',
        'duration_unit',
        'lead_time_days',
        'service_terms',
        'support_terms',
    ];

    protected function casts(): array
    {
        return [
            'duration_value' => 'decimal:2',
            'lead_time_days' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }
}
