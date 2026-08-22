<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: account_dashboard_preferences [V4.3]
 * Preferred dashboard for accounts holding both Buyer and Supplier capabilities.
 */
class AccountDashboardPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'default_mode',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
