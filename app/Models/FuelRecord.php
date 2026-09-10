<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'transaction_id',
        'account_id',
        'fuel_date',
        'odometer',
        'fuel_type',
        'liters',
        'price_per_liter',
        'total_cost',
        'station',
        'notes',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'liters' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'odometer' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // Stats helpers (BUSINESS-RULES §11, §12)
    public static function efficiency(?int $distance, float $liters): ?float
    {
        if ($distance === null || $distance <= 0 || $liters <= 0) {
            return null;
        }
        return $distance / $liters;
    }

    public static function costPerKm(?int $distance, float $totalCost): ?float
    {
        if ($distance === null || $distance <= 0) {
            return null;
        }
        return $totalCost / $distance;
    }
}
