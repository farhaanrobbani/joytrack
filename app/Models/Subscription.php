<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    public const CYCLES = [
        'monthly' => 1,
        'quarterly' => 3,
        'yearly' => 12,
    ];

    public const CYCLE_LABELS = [
        'monthly' => 'Bulanan',
        'quarterly' => '3 Bulan',
        'yearly' => 'Tahunan',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'renewal_cycle',
        'next_renewal_date',
        'reminder_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_renewal_date' => 'date',
        'reminder_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(SubscriptionRenewal::class);
    }

    public function getCycleLabelAttribute(): string
    {
        return self::CYCLE_LABELS[$this->renewal_cycle] ?? strtoupper($this->renewal_cycle);
    }

    /**
     * Tanggal perpanjangan berikutnya = $from + siklus (tanpa overflow tanggal).
     */
    public function nextDateFrom(CarbonInterface $from): CarbonInterface
    {
        $months = self::CYCLES[$this->renewal_cycle] ?? 1;

        return $from->copy()->addMonthsNoOverflow($months);
    }
}
