<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'service_type' => ['required', 'string', 'max:100'],
            'workshop' => ['nullable', 'string', 'max:100'],
            'labor_cost' => ['required', 'numeric', 'min:0'],
            'parts_cost' => ['required', 'numeric', 'min:0'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'next_service_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'next_service_odometer' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'create_transaction' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;
            if ($this->filled('vehicle_id') && ! \App\Models\Vehicle::where('id', $this->vehicle_id)->where('user_id', $userId)->exists()) {
                $validator->errors()->add('vehicle_id', __('Kendaraan tidak valid.'));
            }
            if ($this->filled('account_id') && ! \App\Models\Account::where('id', $this->account_id)->where('user_id', $userId)->exists()) {
                $validator->errors()->add('account_id', __('Akun tidak valid.'));
            }
            if ($this->boolean('create_transaction') && ! $this->filled('account_id')) {
                $validator->errors()->add('account_id', __('Akun wajib jika membuat transaksi keuangan.'));
            }
            if ($this->filled('next_service_odometer') && $this->filled('odometer') && (int) $this->next_service_odometer <= (int) $this->odometer) {
                $validator->errors()->add('next_service_odometer', __('Odometer servis berikutnya harus lebih besar dari odometer saat ini.'));
            }
        });
    }
}
