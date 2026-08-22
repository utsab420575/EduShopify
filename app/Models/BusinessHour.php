<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: business_hours — 0 = Sunday … 6 = Saturday.
 */
class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'account_location_id',
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open'     => 'boolean',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function accountLocation(): BelongsTo
    {
        return $this->belongsTo(AccountLocation::class, 'account_location_id');
    }

    /**
     * Day names for the 0–6 day_of_week column, Sunday first.
     *
     * @return array<int, string>
     */
    public static function dayNames(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    public static function dayName(int $day): string
    {
        return self::dayNames()[$day] ?? 'Unknown';
    }

    public function getDayNameAttribute(): string
    {
        return self::dayName((int) $this->day_of_week);
    }
}
