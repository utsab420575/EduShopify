<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Read-only projection over the rfq_public_summary database view (spec 18.6).
 *
 * Use this for public RFQ search and listings. It carries no buyer identity,
 * budget, address or specifications, so it is safe to expose without running
 * the eligibility checks that full RFQ access requires.
 */
class RfqPublicSummary extends Model
{
    protected $table = 'rfq_public_summary';

    protected $primaryKey = 'rfq_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'rfq_id'                 => 'integer',
        'quotation_deadline'     => 'datetime',
        'qna_deadline'           => 'datetime',
        'expected_delivery_date' => 'date',
        'published_at'           => 'datetime',
        'items_count'            => 'integer',
        'quotations_count'       => 'integer',
        'item_count'             => 'integer',
    ];

    /* ── Read-only guards ───────────────────────────────────────────────── */

    public function save(array $options = []): bool
    {
        throw new LogicException('rfq_public_summary is a database view and cannot be written to.');
    }

    public function delete(): bool
    {
        throw new LogicException('rfq_public_summary is a database view and cannot be written to.');
    }

    /* ── Relationships ──────────────────────────────────────────────────── */

    /**
     * The full RFQ. Reading it still requires the eligibility checks in
     * spec 30.3 — this relation is not itself an authorization decision.
     */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function deliveryCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'delivery_country_id');
    }

    public function deliveryState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'delivery_state_id');
    }

    public function deliveryCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'delivery_city_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    /**
     * Summaries whose quotation deadline has not passed.
     */
    public function scopeStillOpen(Builder $query): Builder
    {
        return $query->where('quotation_deadline', '>', now());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('rfq_number', 'like', "%{$term}%")
                ->orWhere('category_summary', 'like', "%{$term}%");
        });
    }

    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->where('delivery_country_id', $countryId);
    }

    /**
     * The view alone does not guarantee selected-Supplier privacy — callers
     * must still explicitly scope to global-visibility RFQs (spec Part 13).
     */
    public function scopeGlobalVisibility(Builder $query): Builder
    {
        return $query->where('visibility_type', 'global');
    }
}
