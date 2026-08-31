<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_documents (filtered to capability 'buyer') — owned by the
 * buyer ACCOUNT, not a user. Shares the same physical table as
 * SupplierDocument via documentable_type/documentable_id + capability_type_id;
 * see AccountDocument's docblock for why. Unlike SupplierDocument this has
 * no legacy call sites to stay compatible with, so it addresses the account
 * directly through documentable_id rather than a redundant column.
 */
class BuyerDocument extends Model
{
    use HasFactory;

    protected $table = 'account_documents';

    protected static function booted(): void
    {
        static::addGlobalScope('buyerCapability', function (Builder $query) {
            $query->where('capability_type_id', CapabilityType::where('code', 'buyer')->value('id'));
        });

        static::creating(function (self $document) {
            $document->documentable_type ??= 'account';
            $document->capability_type_id ??= CapabilityType::where('code', 'buyer')->value('id');
        });
    }

    protected $fillable = [
        'documentable_id',
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
            'verified_at' => 'datetime',
            'expires_at' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'documentable_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
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

    public function getDocumentNameAttribute(): string
    {
        return $this->document_type_id ? ($this->documentType?->name ?? 'Document') : ($this->custom_name ?? 'Additional Document');
    }
}
