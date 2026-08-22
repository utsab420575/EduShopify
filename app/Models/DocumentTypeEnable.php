<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTypeEnable extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'capability_type_id',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function capabilityType(): BelongsTo
    {
        return $this->belongsTo(CapabilityType::class, 'capability_type_id');
    }
}
