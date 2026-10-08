<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'renewed_at',
        'previous_date',
        'new_date',
        'amount',
        'account_id',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'renewed_at' => 'date',
        'previous_date' => 'date',
        'new_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
