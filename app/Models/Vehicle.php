<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'license_plate',
        'brand',
        'model',
        'year',
        'color',
        'vehicle_type',
        'chassis_number',
        'engine_number',
        'purchase_date',
        'purchase_price',
        'current_odometer',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'year' => 'integer',
        'current_odometer' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
