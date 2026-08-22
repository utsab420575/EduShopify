<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table: document_types — one catalogue applied to every supplier.
 */
class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'accepted_formats',
        'max_size_kb',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'accepted_formats' => 'array',
        'max_size_kb'      => 'integer',
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
    ];

    public function supplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'document_type_id');
    }

    public function capabilityEnables(): HasMany
    {
        return $this->hasMany(DocumentTypeEnable::class, 'document_type_id');
    }

    public function enables(): HasMany
    {
        return $this->capabilityEnables();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Accept attribute string for an HTML file input, e.g. ".pdf,.jpg,.png".
     */
    public function getAcceptAttribute(): string
    {
        if (empty($this->accepted_formats)) {
            return '*';
        }

        return implode(',', array_map(fn ($ext) => '.' . ltrim($ext, '.'), $this->accepted_formats));
    }

    /**
     * Max upload size in bytes, for validation rules.
     */
    public function getMaxSizeBytesAttribute(): int
    {
        return ($this->max_size_kb ?? 5120) * 1024;
    }
}
