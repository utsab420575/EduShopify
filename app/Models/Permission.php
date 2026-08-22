<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Table: permissions — Spatie's permission table extended with the V4.3
 * metadata columns. Permissions are global abilities; account scoping happens
 * on the role, not here.
 */
class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'group_name',
        'capability_scope',
        'description',
        'is_sensitive',
        'is_owner_only',
        'is_assignable',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive'  => 'boolean',
            'is_owner_only' => 'boolean',
            'is_assignable' => 'boolean',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ];
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Permissions an account owner may hand out: active, assignable and not
     * platform-scoped.
     */
    public function scopeDelegatable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_assignable', true)
            ->where('capability_scope', '!=', 'platform');
    }

    public function scopeScope(Builder $query, string $capabilityScope): Builder
    {
        return $query->where('capability_scope', $capabilityScope);
    }

    public function scopeGroup(Builder $query, string $groupName): Builder
    {
        return $query->where('group_name', $groupName);
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isPlatformScoped(): bool
    {
        return $this->capability_scope === 'platform';
    }

    /**
     * Owner-only permissions still require the owner state in account_members;
     * Spatie only stores the grant.
     */
    public function requiresOwner(): bool
    {
        return (bool) $this->is_owner_only;
    }
}
