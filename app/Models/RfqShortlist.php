<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: rfq_shortlists — buyer-side shortlisting of received quotations.
 */
class RfqShortlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'quotation_id',
        'buyer_account_id',
        'shortlisted_by_user_id',
        'notes',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function buyerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_account_id');
    }

    public function shortlistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shortlisted_by_user_id');
    }
}
