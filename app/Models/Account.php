<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'initial_balance',
        'current_balance',
        'credit_limit',
        'billing_day',
        'due_day',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'billing_day' => 'integer',
        'due_day' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the account type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'bank' => __('Bank'),
            'cash' => __('Cash'),
            'ewallet' => __('E-wallet'),
            'savings' => __('Savings'),
            'other' => __('Other'),
            'credit' => __('Kartu Kredit / Paylater'),
            default => __('Unknown'),
        };
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function getUsedCreditAttribute(): ?float
    {
        if (! $this->isCredit()) {
            return null;
        }

        return max(0, -((float) $this->current_balance));
    }

    public function getAvailableCreditAttribute(): ?float
    {
        if (! $this->isCredit() || $this->credit_limit === null) {
            return null;
        }

        return (float) $this->credit_limit - (float) $this->used_credit;
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
