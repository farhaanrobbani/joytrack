<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'fuel_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'liters' => ['required', 'numeric', 'gt:0'],
            'price_per_liter' => ['required', 'numeric', 'gt:0'],
            'total_cost' => ['required', 'numeric', 'gt:0'],
            'station' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'create_transaction' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;
            if ($this->filled('vehicle_id')) {
                if (! \App\Models\Vehicle::where('id', $this->vehicle_id)->where('user_id', $userId)->exists()) {
                    $validator->errors()->add('vehicle_id', __('Kendaraan tidak valid.'));
                } else {
                    // odometer monotonic check vs last record (optional, allow first)
                    $last = \App\Models\FuelRecord::where('vehicle_id', $this->vehicle_id)->orderByDesc('odometer')->first();
                    if ($last && (int) $this->odometer < (int) $last->odometer) {
                        $validator->errors()->add('odometer', __('Odometer tidak boleh lebih kecil dari sebelumnya (:value km).', ['value' => number_format($last->odometer,0,',','.')]));
                    }
                }
            }
            if ($this->filled('account_id') && ! \App\Models\Account::where('id', $this->account_id)->where('user_id', $userId)->exists()) {
                $validator->errors()->add('account_id', __('Akun tidak valid.'));
            }
            if ($this->boolean('create_transaction') && ! $this->filled('account_id')) {
                $validator->errors()->add('account_id', __('Akun wajib jika membuat transaksi keuangan.'));
            }
        });
    }
}
