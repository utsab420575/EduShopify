<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapabilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const BUYER = 'buyer';
    public const SUPPLIER = 'supplier';

    public function accountCapabilities(): HasMany
    {
        return $this->hasMany(AccountCapability::class, 'capability_type_id');
    }

    public function documentTypeEnables(): HasMany
    {
        return $this->hasMany(DocumentTypeEnable::class, 'capability_type_id');
    }
}
