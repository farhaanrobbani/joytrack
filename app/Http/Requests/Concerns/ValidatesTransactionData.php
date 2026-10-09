<?php

namespace App\Http\Requests\Concerns;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Validation\Rule;

trait ValidatesTransactionData
{
    public static function transactionRules(array $data): array
    {
        return [
            'type' => ['required', 'in:income,expense,transfer'],
            'account_id' => ['required', 'exists:accounts,id'],
            'destination_account_id' => ['required_if:type,transfer', 'nullable', 'exists:accounts,id', 'different:account_id'],
            'category_id' => [
                Rule::requiredIf(fn () => in_array($data['type'] ?? null, ['income', 'expense'], true)),
                'nullable',
                'exists:categories,id',
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public static function transactionMessages(): array
    {
        return [
            'type.required' => __('Jenis transaksi wajib dipilih.'),
            'account_id.required' => __('Akun wajib dipilih.'),
            'destination_account_id.required_if' => __('Akun tujuan wajib untuk transfer.'),
            'destination_account_id.different' => __('Akun tujuan harus berbeda dari akun asal.'),
            'category_id.required' => __('Kategori wajib untuk pemasukan/pengeluaran.'),
            'amount.required' => __('Nominal wajib diisi.'),
            'amount.gt' => __('Nominal harus lebih dari 0.'),
            'transaction_date.required' => __('Tanggal wajib diisi.'),
        ];
    }

    public static function checkTransactionOwnership(mixed $validator, int $userId, array $data): void
    {
        if (! empty($data['account_id'])) {
            $exists = Account::where('id', $data['account_id'])->where('user_id', $userId)->exists();
            if (! $exists) {
                $validator->errors()->add('account_id', __('Akun tidak valid.'));
            }
        }
        if (! empty($data['destination_account_id'])) {
            $exists = Account::where('id', $data['destination_account_id'])->where('user_id', $userId)->exists();
            if (! $exists) {
                $validator->errors()->add('destination_account_id', __('Akun tujuan tidak valid.'));
            }
        }
        if (! empty($data['category_id'])) {
            $cat = Category::where('id', $data['category_id'])->where('user_id', $userId)->first();
            if (! $cat) {
                $validator->errors()->add('category_id', __('Kategori tidak valid.'));
            } elseif (! empty($data['type']) && $cat->type !== $data['type']) {
                $validator->errors()->add('category_id', __('Kategori tidak sesuai dengan jenis transaksi.'));
            }
        }
    }
}
