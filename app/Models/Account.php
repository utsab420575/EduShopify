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
 * Table: accounts
 *
 * The account is both the business identity and the Spatie Teams authorization
 * context. Supplier-owned records key on supplier_account_id and buyer-owned
 * records on buyer_account_id; both point here.
 */
class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_number',
        'account_type',
        'is_system_account',
        'display_name',
        'slug',
        'status',
        'primary_owner_user_id',
        'converted_from_individual_at',
        'approved_at',
        'approved_by_user_id',
        'suspended_at',
        'suspended_by_user_id',
        'suspension_reason',
        'deletion_requested_at',
        'deleted_by_user_id',
        'deletion_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_system_account'            => 'boolean',
            'converted_from_individual_at' => 'datetime',
            'approved_at'                  => 'datetime',
            'suspended_at'                 => 'datetime',
            'deletion_requested_at'        => 'datetime',
            'deleted_at'                   => 'datetime',
        ];
    }

    /* ── Users ──────────────────────────────────────────────────────────── */

    public function primaryOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_owner_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_user_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(AccountMember::class, 'account_id');
    }

    public function memberInvitations(): HasMany
    {
        return $this->hasMany(AccountMemberInvitation::class, 'account_id');
    }

    public function ownershipTransfers(): HasMany
    {
        return $this->hasMany(AccountOwnershipTransfer::class, 'account_id');
    }

    /* ── Capabilities, conversion, preferences ──────────────────────────── */

    public function capabilities(): HasMany
    {
        return $this->hasMany(AccountCapability::class, 'account_id');
    }

    public function buyerCapability(): HasOne
    {
        return $this->hasOne(AccountCapability::class, 'account_id')->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'));
    }

    public function supplierCapability(): HasOne
    {
        return $this->hasOne(AccountCapability::class, 'account_id')->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'));
    }

    public function conversionRequests(): HasMany
    {
        return $this->hasMany(AccountConversionRequest::class, 'account_id');
    }

    public function typeChanges(): HasMany
    {
        return $this->hasMany(AccountTypeChange::class, 'account_id');
    }

    public function dashboardPreference(): HasOne
    {
        return $this->hasOne(AccountDashboardPreference::class, 'account_id');
    }

    public function roleRequests(): HasMany
    {
        return $this->hasMany(RoleRequest::class, 'account_id');
    }

    /* ── Profiles & locations ───────────────────────────────────────────── */

    public function buyerProfile(): HasOne
    {
        return $this->hasOne(BuyerProfile::class, 'account_id');
    }

    public function supplierProfile(): HasOne
    {
        return $this->hasOne(SupplierProfile::class, 'account_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(AccountLocation::class, 'account_id');
    }

    /* ── Supplier-side records (supplier_account_id) ────────────────────── */

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(SupplierServiceArea::class, 'supplier_account_id');
    }

    public function supplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_account_id');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(SupplierGallery::class, 'supplier_account_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(SupplierVideo::class, 'supplier_account_id');
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class, 'supplier_account_id');
    }

    public function supplierTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierType::class,
            'supplier_supplier_type',
            'supplier_account_id',
            'supplier_type_id'
        )->withPivot('is_primary')->withTimestamps();
    }

    public function exhibitions(): BelongsToMany
    {
        return $this->belongsToMany(
            Exhibition::class,
            'exhibition_supplier',
            'supplier_account_id',
            'exhibition_id'
        )->withPivot(['booth_number', 'participation_year'])->withTimestamps();
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'supplier_account_id');
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class, 'supplier_account_id');
    }

    public function customUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'supplier_account_id');
    }

    public function categorySuggestions(): HasMany
    {
        return $this->hasMany(CategorySuggestion::class, 'supplier_account_id');
    }

    public function attributeSuggestions(): HasMany
    {
        return $this->hasMany(AttributeSuggestion::class, 'supplier_account_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'supplier_account_id');
    }

    /**
     * The single subscription that currently grants access. Trialing counts as
     * current for RFQ eligibility, per the spec.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'supplier_account_id')
            ->whereIn('status', ['active', 'trialing'])
            ->latestOfMany();
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'supplier_account_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'supplier_account_id');
    }

    public function rfqQueueEntries(): HasMany
    {
        return $this->hasMany(RfqSupplierQueue::class, 'supplier_account_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'supplier_account_id');
    }

    public function contactInquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class, 'supplier_account_id');
    }

    /* ── Buyer-side records (buyer_account_id) ──────────────────────────── */

    public function buyerTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            BuyerType::class,
            'buyer_buyer_type',
            'buyer_account_id',
            'buyer_type_id'
        )->withPivot('is_primary')->withTimestamps();
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'buyer_account_id');
    }

    public function shortlists(): HasMany
    {
        return $this->hasMany(RfqShortlist::class, 'buyer_account_id');
    }

    public function writtenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'buyer_account_id');
    }

    /* ── Both sides ─────────────────────────────────────────────────────── */

    public function buyerAwards(): HasMany
    {
        return $this->hasMany(Award::class, 'buyer_account_id');
    }

    public function supplierAwards(): HasMany
    {
        return $this->hasMany(Award::class, 'supplier_account_id');
    }

    public function buyerPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'buyer_account_id');
    }

    public function supplierPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_account_id');
    }

    public function conversationParticipations(): HasMany
    {
        return $this->hasMany(ConversationAccount::class, 'account_id');
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_accounts', 'account_id', 'conversation_id')
            ->withPivot(['participant_capability', 'joined_at', 'left_at', 'is_active'])
            ->withTimestamps();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'account_id');
    }

    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class, 'account_id');
    }

    /* ── Spatie Teams ───────────────────────────────────────────────────── */

    /**
     * Account-specific (non-global) Spatie roles owned by this account.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'account_id');
    }

    /* ── Scopes & helpers ───────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeMarketplace(Builder $query): Builder
    {
        return $query->where('is_system_account', false);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOrganization(): bool
    {
        return $this->account_type === 'organization';
    }

    /**
     * True when the account holds the given capability in the active state.
     */
    public function hasActiveCapability(string $capability): bool
    {
        return $this->capabilities
            ->first(fn (AccountCapability $c) => $c->capabilityType?->code === $capability && $c->status === 'active') !== null;
    }

    /**
     * True when the capability exists in any state — including draft, pending
     * and rejected. Use this to decide whether to show an onboarding journey,
     * and hasActiveCapability() to decide whether to allow a business action.
     */
    public function hasCapability(string $capability): bool
    {
        return $this->capabilities
            ->first(fn (AccountCapability $c) => $c->capabilityType?->code === $capability) !== null;
    }

    public function capabilityStatus(string $capability): ?string
    {
        return $this->capabilities
            ->first(fn (AccountCapability $c) => $c->capabilityType?->code === $capability)?->status;
    }

    public function isBuyer(): bool
    {
        return $this->hasActiveCapability('buyer');
    }

    public function isSupplier(): bool
    {
        return $this->hasActiveCapability('supplier');
    }

    /**
     * A supplier subscription that is active or trialing and has not lapsed.
     * Required before RFQ access and quotation submission.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription?->isActive() ?? false;
    }

    /**
     * A free plan may be taken once. The supplier capability must be approved
     * first — the spec forbids activating a subscription before that.
     */
    public function isEligibleForFreePlan(): bool
    {
        if (! $this->hasActiveCapability('supplier') || $this->hasActiveSubscription()) {
            return false;
        }

        return ! $this->subscriptions()
            ->whereHas('plan', fn ($q) => $q->where('is_free', true))
            ->exists();
    }
}
