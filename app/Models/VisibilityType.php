<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisibilityType extends Model
{
    use HasFactory;

    protected $table = 'visibility_types';

    protected $fillable = [
        'name',
        'code',
        'engine_type',
        'max_suppliers',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
            'max_suppliers' => 'integer',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'visibility_type_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInvited(Builder $query): Builder
    {
        return $query->where('engine_type', 'invited');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('engine_type', 'open');
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    public function isInvited(): bool
    {
        return $this->engine_type === 'invited';
    }

    public function isOpen(): bool
    {
        return $this->engine_type === 'open';
    }

    public function isDirect(): bool
    {
        return $this->code === 'direct' || $this->max_suppliers === 1;
    }
}
