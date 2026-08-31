<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: buyer_galleries — mirrors SupplierGallery exactly, buyer-scoped.
 */
class BuyerGallery extends Model
{
    use HasFactory;

    protected $table = 'buyer_galleries';

    protected $fillable = [
        'buyer_account_id',
        'media_id',
        'image_path',
        'caption',
        'alt_text',
        'sort_order',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
