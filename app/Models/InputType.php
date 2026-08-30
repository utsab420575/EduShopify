<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: input_types — Catalog attribute input format definition (text, select, multi_select, etc.)
 */
class InputType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'has_options',
        'is_multiple',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'has_options' => 'boolean',
            'is_multiple' => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'input_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
