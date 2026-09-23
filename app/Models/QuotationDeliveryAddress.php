<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: quotation_delivery_addresses — one delivery location for a
 * quotation, including the one at sort_order 0 ("Address 1"). Seeded once
 * from the RFQ's own delivery address(es) when the quotation is first
 * created (QuotationService::copyDeliveryAddressesFromRfq()), then fully
 * independent of rfqs/rfq_delivery_addresses from that point on.
 */
class QuotationDeliveryAddress extends Model
{
    protected $fillable = [
        'quotation_id',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
