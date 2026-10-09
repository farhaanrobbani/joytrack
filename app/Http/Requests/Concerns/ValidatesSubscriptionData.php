<?php

namespace App\Http\Requests\Concerns;

use App\Models\Account;
use App\Models\Subscription;

trait ValidatesSubscriptionData
{
    public static function subscriptionRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'renewal_cycle' => ['required', 'string', 'in:'.implode(',', array_keys(Subscription::CYCLES))],
            'next_renewal_date' => ['required', 'date'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:90'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public static function renewRules(): array
    {
        return [
            'create_transaction' => ['sometimes', 'boolean'],
            'account_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function checkRenewData(mixed $validator, int $userId, array $data, ?Subscription $subscription): void
    {
        if (! empty($data['account_id']) && ! Account::where('id', $data['account_id'])->where('user_id', $userId)->exists()) {
            $validator->errors()->add('account_id', __('Akun tidak valid.'));
        }

        if (! empty($data['create_transaction'])) {
            if (empty($data['account_id'])) {
                $validator->errors()->add('account_id', __('Akun wajib jika membuat transaksi keuangan.'));
            }
            if (empty($data['amount']) && (! $subscription || $subscription->amount === null)) {
                $validator->errors()->add('amount', __('Nominal wajib jika membuat transaksi keuangan.'));
            }
        }
    }
}
