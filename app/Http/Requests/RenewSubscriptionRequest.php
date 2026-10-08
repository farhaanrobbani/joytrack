<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class RenewSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'create_transaction' => ['sometimes', 'boolean'],
            'account_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;

            if ($this->filled('account_id') && ! Account::where('id', $this->account_id)->where('user_id', $userId)->exists()) {
                $validator->errors()->add('account_id', __('Akun tidak valid.'));
            }

            if ($this->boolean('create_transaction')) {
                if (! $this->filled('account_id')) {
                    $validator->errors()->add('account_id', __('Akun wajib jika membuat transaksi keuangan.'));
                }

                $subscription = $this->route('subscription');
                if (! $this->filled('amount') && (! $subscription || $subscription->amount === null)) {
                    $validator->errors()->add('amount', __('Nominal wajib jika membuat transaksi keuangan.'));
                }
            }
        });
    }
}
