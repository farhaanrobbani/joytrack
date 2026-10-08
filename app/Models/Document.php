<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    public const TYPES = ['stnk', 'sim', 'pajak', 'asuransi', 'paspor', 'lainnya'];

    public const TYPE_LABELS = [
        'stnk' => 'STNK',
        'sim' => 'SIM',
        'pajak' => 'Pajak',
        'asuransi' => 'Asuransi',
        'paspor' => 'Paspor',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'name',
        'document_type',
        'expiry_date',
        'reminder_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'reminder_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->document_type] ?? strtoupper($this->document_type);
    }
}
