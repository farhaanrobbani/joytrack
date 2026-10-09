<?php

namespace App\Http\Requests\Concerns;

use App\Models\Account;
use App\Models\FuelRecord;
use App\Models\Vehicle;

trait ValidatesFuelRecordData
{
    public static function fuelRecordRules(bool $withTransaction = false): array
    {
        $rules = [
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
        ];

        if ($withTransaction) {
            $rules['create_transaction'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    public static function checkFuelRecordData(mixed $validator, int $userId, array $data, bool $checkOdometer = false, bool $checkCreateTransaction = false): void
    {
        if (! empty($data['vehicle_id'])) {
            if (! Vehicle::where('id', $data['vehicle_id'])->where('user_id', $userId)->exists()) {
                $validator->errors()->add('vehicle_id', __('Kendaraan tidak valid.'));
            } elseif ($checkOdometer && ! empty($data['odometer'])) {
                $last = FuelRecord::where('vehicle_id', $data['vehicle_id'])->orderByDesc('odometer')->first();
                if ($last && (int) $data['odometer'] < (int) $last->odometer) {
                    $validator->errors()->add('odometer', __('Odometer tidak boleh lebih kecil dari sebelumnya (:value km).', ['value' => number_format($last->odometer, 0, ',', '.')]));
                }
            }
        }
        if (! empty($data['account_id']) && ! Account::where('id', $data['account_id'])->where('user_id', $userId)->exists()) {
            $validator->errors()->add('account_id', __('Akun tidak valid.'));
        }
        if ($checkCreateTransaction && ! empty($data['create_transaction']) && empty($data['account_id'])) {
            $validator->errors()->add('account_id', __('Akun wajib jika membuat transaksi keuangan.'));
        }
    }
}
