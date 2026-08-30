<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMessagingPreference extends Model
{
    use HasFactory;

    protected $table = 'user_messaging_preferences';

    protected $fillable = [
        'user_id',
        'sound_enabled',
        'browser_notifications_enabled',
        'unread_email_enabled',
        'unread_email_delay_hours',
        'last_reminder_sent_at',
        'last_digest_hash',
    ];

    protected function casts(): array
    {
        return [
            'sound_enabled'                 => 'boolean',
            'browser_notifications_enabled' => 'boolean',
            'unread_email_enabled'          => 'boolean',
            'unread_email_delay_hours'      => 'integer',
            'last_reminder_sent_at'         => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function forUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            [
                'sound_enabled'                 => true,
                'browser_notifications_enabled' => false,
                'unread_email_enabled'          => false,
                'unread_email_delay_hours'      => 24,
            ]
        );
    }
}
