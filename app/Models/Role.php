<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Table: roles — Spatie's role table extended with the V4.3 metadata columns.
 *
 * account_id is Spatie's team key: NULL means a global role available to every
 * account; a value means a custom role owned by that account. Global roles are
 * maintained by Platform Admin and must be cloned, never edited, by an account.
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'account_id',
        'name',
        'guard_name',
        'display_name',
        'capability_scope',
        'description',
        'is_system',
        'is_owner_role',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_system'     => 'boolean',
            'is_owner_role' => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    /**
     * Roles available to every account.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('account_id');
    }

    /**
     * Roles usable inside one account: its own plus the global set.
     */
    public function scopeUsableBy(Builder $query, int $accountId): Builder
    {
        return $query->where(function (Builder $q) use ($accountId) {
            $q->whereNull('account_id')->orWhere('account_id', $accountId);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeScope(Builder $query, string $capabilityScope): Builder
    {
        return $query->where('capability_scope', $capabilityScope);
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isGlobal(): bool
    {
        return $this->account_id === null;
    }

    /**
     * Global roles are immutable for normal organizations.
     */
    public function isEditableBy(int $accountId): bool
    {
        return $this->account_id === $accountId;
    }
}
