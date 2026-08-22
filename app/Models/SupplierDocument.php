<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: supplier_documents — owned by the supplier ACCOUNT, not a user.
 */
class SupplierDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'document_type_id',
        'custom_name',
        'file_path',
        'original_name',
        'mime_type',
        'file_size_kb',
        'status',
        'rejection_reason',
        'uploaded_by_user_id',
        'verified_by_user_id',
        'verified_at',
        'expires_at',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'file_size_kb' => 'integer',
            'verified_at'  => 'datetime',
            'expires_at'   => 'date',
            'is_current'   => 'boolean',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getDocumentNameAttribute(): string
    {
        return $this->document_type_id ? ($this->documentType?->name ?? 'Document') : ($this->custom_name ?? 'Additional Document');
    }
}
