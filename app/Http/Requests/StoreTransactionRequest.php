<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense,transfer'],
            'account_id' => ['required', 'exists:accounts,id'],
            'destination_account_id' => ['required_if:type,transfer', 'nullable', 'exists:accounts,id', 'different:account_id'],
            'category_id' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), ['income', 'expense'])),
                'nullable',
                'exists:categories,id',
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;

            if ($this->filled('account_id')) {
                $exists = \App\Models\Account::where('id', $this->account_id)->where('user_id', $userId)->exists();
                if (! $exists) {
                    $validator->errors()->add('account_id', __('Akun tidak valid.'));
                }
            }
            if ($this->filled('destination_account_id')) {
                $exists = \App\Models\Account::where('id', $this->destination_account_id)->where('user_id', $userId)->exists();
                if (! $exists) {
                    $validator->errors()->add('destination_account_id', __('Akun tujuan tidak valid.'));
                }
            }
            if ($this->filled('category_id')) {
                $cat = \App\Models\Category::where('id', $this->category_id)->where('user_id', $userId)->first();
                if (! $cat) {
                    $validator->errors()->add('category_id', __('Kategori tidak valid.'));
                } elseif ($this->filled('type') && $cat->type !== $this->type) {
                    $validator->errors()->add('category_id', __('Kategori tidak sesuai dengan jenis transaksi.'));
                }
            }
        });
    }

    public function messages(): array
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
}
