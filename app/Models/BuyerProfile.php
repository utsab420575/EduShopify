<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: buyer_profiles — belongs to an account, never directly to a user.
 */
class BuyerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'buyer_type_id',
        'display_name',
        'organization_name',
        'contact_person',
        'position',
        'email',
        'phone',
        'website',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'logo',
        'profile_photo',
        'tax_id',
        'bio',
        'procurement_info',
        'additional_data',
        'profile_completed_at',
        'current_step',
        'max_step_reached',
    ];

    protected function casts(): array
    {
        return [
            'additional_data'      => 'array',
            'profile_completed_at' => 'datetime',
            'current_step'         => 'integer',
            'max_step_reached'     => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function buyerType(): BelongsTo
    {
        return $this->belongsTo(BuyerType::class, 'buyer_type_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function isComplete(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function isDraft(): bool
    {
        return $this->profile_completed_at === null;
    }
}
