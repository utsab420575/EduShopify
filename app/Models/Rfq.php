<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: rfqs — owned by the buyer ACCOUNT (buyer_account_id).
 *
 * Delivery is denormalized onto the row (country/state/city/address/lat/lng);
 * there is no delivery location foreign key.
 */
class Rfq extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rfqs';

    protected static function booted(): void
    {
        static::saving(function (Rfq $rfq) {
            if (! $rfq->visibility_type_id) {
                $rfq->visibility_type_id = VisibilityType::firstOrCreate(
                    ['code' => 'open_matching'],
                    ['name' => 'Open RFQ', 'engine_type' => 'open', 'is_active' => true]
                )->id;
            }
        });
    }

    public function fill(array $attributes)
    {
        if (isset($attributes['visibility_type']) && ! isset($attributes['visibility_type_id'])) {
            $val = $attributes['visibility_type'];
            if (is_numeric($val)) {
                $attributes['visibility_type_id'] = (int)$val;
            } else {
                $code = match ($val) {
                    'global'             => 'open_matching',
                    'selected_suppliers' => 'invited',
                    default              => $val,
                };
                $attributes['visibility_type_id'] = VisibilityType::firstOrCreate(
                    ['code' => $code],
                    [
                        'name'        => ucfirst(str_replace('_', ' ', $code)),
                        'engine_type' => in_array($code, ['direct', 'invited'], true) ? 'invited' : 'open',
                        'is_active'   => true,
                    ]
                )->id;
            }
            unset($attributes['visibility_type']);
        }

        return parent::fill($attributes);
    }

    protected $fillable = [
        'rfq_number',
        'buyer_account_id',
        'created_by_user_id',
        'visibility_type_id',
        'source_listing_id',
        'title',
        'description',
        'currency_code',
        'budget_min',
        'budget_max',
        'delivery_country_id',
        'delivery_state_id',
        'delivery_city_id',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'allow_partial_quotation',
        'allow_alternative_products',
        'quotation_deadline',
        'qna_deadline',
        'expected_delivery_date',
        'status',
        'current_version_no',
        'published_at',
        'items_count',
        'quotations_count',
        'approved_by_user_id',
        'approved_at',
        'closed_at',
        'awarded_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'budget_min'                 => 'decimal:2',
            'budget_max'                 => 'decimal:2',
            'delivery_latitude'          => 'decimal:7',
            'delivery_longitude'         => 'decimal:7',
            'allow_partial_quotation'    => 'boolean',
            'allow_alternative_products' => 'boolean',
            'quotation_deadline'         => 'datetime',
            'qna_deadline'               => 'datetime',
            'expected_delivery_date'     => 'date',
            'current_version_no'         => 'integer',
            'published_at'               => 'datetime',
            'items_count'                => 'integer',
            'quotations_count'           => 'integer',
            'approved_at'                => 'datetime',
            'closed_at'                  => 'datetime',
            'awarded_at'                 => 'datetime',
            'cancelled_at'               => 'datetime',
        ];
    }

    /* ── Ownership ──────────────────────────────────────────────────────── */

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function visibilityType(): BelongsTo
    {
        return $this->belongsTo(VisibilityType::class, 'visibility_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function sourceListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'source_listing_id');
    }

    /* ── Delivery geography ─────────────────────────────────────────────── */

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

    /* ── Contents & targeting ───────────────────────────────────────────── */

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id');
    }

    public function selectedSuppliers(): HasMany
    {
        return $this->hasMany(RfqSelectedSupplier::class, 'rfq_id');
    }

    /**
     * Supplier accounts the buyer explicitly invited.
     */
    public function invitedSupplierAccounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'rfq_selected_suppliers', 'rfq_id', 'supplier_account_id')
            ->withPivot(['selected_by_user_id', 'invited_at'])
            ->withTimestamps();
    }

    /**
     * rfq_target_filters holds zero or more filter rows per RFQ.
     */
    public function targetFilters(): HasMany
    {
        return $this->hasMany(RfqTargetFilter::class, 'rfq_id');
    }

    public function supplierQueue(): HasMany
    {
        return $this->hasMany(RfqSupplierQueue::class, 'rfq_id');
    }

    /* ── Interaction ────────────────────────────────────────────────────── */

    public function questions(): HasMany
    {
        return $this->hasMany(RfqQuestion::class, 'rfq_id');
    }

    public function deadlineExtensions(): HasMany
    {
        return $this->hasMany(RfqDeadlineExtension::class, 'rfq_id');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(RfqChangeLog::class, 'rfq_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'rfq_id');
    }

    public function shortlists(): HasMany
    {
        return $this->hasMany(RfqShortlist::class, 'rfq_id');
    }

    /* ── Outcome ────────────────────────────────────────────────────────── */

    /**
     * Every award attempt. Phase 1 allows one winner but several attempts,
     * so this is a hasMany, not a hasOne.
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'rfq_id');
    }

    public function latestAward(): HasOne
    {
        return $this->hasOne(Award::class, 'rfq_id')->latestOfMany('award_attempt_no');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'rfq_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'rfq_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeGlobalVisibility(Builder $query): Builder
    {
        return $query->whereHas('visibilityType', fn ($q) => $q->where('engine_type', 'open'));
    }

    public function scopeAcceptingQuotations(Builder $query): Builder
    {
        return $query->where('status', 'open')->where('quotation_deadline', '>', now());
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInvited(): bool
    {
        return $this->getRelationValue('visibilityType')?->engine_type === 'invited';
    }

    public function isDirect(): bool
    {
        $vt = $this->getRelationValue('visibilityType');
        return $vt?->code === 'direct' || $vt?->max_suppliers === 1;
    }

    public function isOpenMarketplace(): bool
    {
        return $this->getRelationValue('visibilityType')?->engine_type === 'open';
    }

    /**
     * Backward-compatible code accessor so legacy calls like `$rfq->visibility_type`
     * return standard code ('global' / 'selected_suppliers' / custom code).
     */
    public function getVisibilityTypeAttribute(): ?string
    {
        $vt = $this->getRelationValue('visibilityType');
        if (is_object($vt)) {
            return match ($vt->code) {
                'open_matching', 'broadcast_all' => 'global',
                'invited', 'direct'              => 'selected_suppliers',
                default                          => $vt->code,
            };
        }

        return is_string($vt) ? $vt : null;
    }

    public function deadlinePassed(): bool
    {
        return $this->quotation_deadline !== null && $this->quotation_deadline->isPast();
    }

    public function qnaClosed(): bool
    {
        return $this->qna_deadline !== null && $this->qna_deadline->isPast();
    }

    public function acceptsQuotations(): bool
    {
        return $this->isOpen() && ! $this->deadlinePassed();
    }
}
