<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: contact_inquiries — guest enquiries.
 * supplier_account_id = null means a general platform enquiry.
 */
class ContactInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_number',
        'supplier_account_id',
        'listing_id',
        'name',
        'email',
        'phone',
        'organization',
        'subject',
        'message',
        'status',
        'handled_by_user_id',
        'handled_at',
        'replied_at',
        'closed_at',
        'ip_address',
        'user_agent',
        'source_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'replied_at' => 'datetime',
            'closed_at'  => 'datetime',
            'metadata'   => 'array',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    public function scopeUnhandled(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'read']);
    }

    public function isPlatformInquiry(): bool
    {
        return $this->supplier_account_id === null;
    }
}
