<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Table: services — a service listed by any account (buyer, supplier, or
 * otherwise). serviceable is polymorphic so it isn't tied to one account type.
 */
class Service extends Model
{
    protected $fillable = [
        'serviceable_type',
        'serviceable_id',
        'icon_id',
        'title',
        'description',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class, 'icon_id');
    }
}
