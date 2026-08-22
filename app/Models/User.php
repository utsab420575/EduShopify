<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

/**
 * Table: users
 *
 * V4.3 note: users.type does not exist. A human is a login only; Buyer/Supplier
 * eligibility lives on the account via account_capabilities, and platform staff
 * status is expressed through Spatie roles scoped to the reserved system account.
 */
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'locale',
        'currency_code',
        'email_verified_at',
        'phone_verified_at',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /* ── Account & membership ───────────────────────────────────────────── */

    /**
     * A user belongs to exactly one account (account_members has unique(user_id)).
     */
    public function accountMember(): HasOne
    {
        return $this->hasOne(AccountMember::class, 'user_id');
    }

    /**
     * The single account this user belongs to, resolved through account_members.
     */
    public function account(): HasOneThrough
    {
        return $this->hasOneThrough(
            Account::class,
            AccountMember::class,
            'user_id',    // FK on account_members referencing users
            'id',         // PK on accounts
            'id',         // local key on users
            'account_id'  // FK on account_members referencing accounts
        );
    }

    /* ── Authentication artefacts ───────────────────────────────────────── */

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class, 'user_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'user_id');
    }

    /* ── Records this user acted on ─────────────────────────────────────── */

    public function createdListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'created_by_user_id');
    }

    public function createdRfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'created_by_user_id');
    }

    public function submittedQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'submitted_by_user_id');
    }

    public function uploadedSupplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'uploaded_by_user_id');
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_admin_user_id');
    }

    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class, 'saved_by_user_id');
    }

    public function conversationStates(): HasMany
    {
        return $this->hasMany(ConversationUserState::class, 'user_id');
    }

    public function conversationAdminSeats(): HasMany
    {
        return $this->hasMany(ConversationAdminUser::class, 'user_id');
    }

    public function roleRequests(): HasMany
    {
        return $this->hasMany(RoleRequest::class, 'requested_by_user_id');
    }

    /* ── Status helpers ─────────────────────────────────────────────────── */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdmin(): bool
    {
        $account = $this->accountMember?->account;
        return $account?->is_system_account
            && $this->hasAnyRole(['super_admin', 'admin', 'admin_staff', 'moderator', 'support_agent', 'finance_staff']);
    }

    public function isBuyer(): bool
    {
        $account = $this->accountMember?->account;
        return $account?->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists() ?? false;
    }

    public function isSupplier(): bool
    {
        $account = $this->accountMember?->account;
        return $account?->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists() ?? false;
    }

    /**
     * Resolve the account this user acts within and set it as the Spatie team
     * context, so role and permission checks resolve against the right account.
     *
     * Returns null when the user has no usable membership.
     */
    public function activateTeamContext(): ?Account
    {
        $member = $this->relationLoaded('accountMember')
            ? $this->accountMember
            : $this->accountMember()->with('account')->first();

        if (! $member || $member->status !== 'active') {
            return null;
        }

        $account = $member->account;

        if (! $account || $account->status !== 'active') {
            return null;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($account->getKey());

        // Role/permission relations cached under a previous team must be dropped.
        $this->unsetRelation('roles')->unsetRelation('permissions');

        return $account;
    }

    /* ── Filament panel access ──────────────────────────────────────────── */

    /**
     * Panel access follows the spec's authorization order: user status, account
     * status, membership status, then capability (Buyer/Supplier) or platform
     * role for staff. Buyer and Supplier are capabilities, never Spatie roles.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $account = $this->activateTeamContext();

        if (! $account) {
            return false;
        }

        return match ($panel->getId()) {
            // Admin panel: reserved system account + one of the platform staff roles
            'admin' => $account->is_system_account
                && $this->hasAnyRole(['super_admin', 'admin', 'admin_staff', 'moderator', 'support_agent', 'finance_staff']),

            // Supplier panel: verified + account has a supplier capability row in ANY status
            // Individual supplier pages enforce their own capability status checks
            'supplier' => $this->hasVerifiedEmail()
                && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists(),

            // Buyer panel: verified + account has a buyer capability row in ANY status
            // This allows draft/pending/revision/rejected buyers to access their onboarding dashboard
            // Individual buyer pages/actions enforce their own capability status policies
            'buyer' => $this->hasVerifiedEmail()
                && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists(),

            default => false,
        };
    }
}
