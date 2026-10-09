<?php

namespace App\Http\Requests\Concerns;

use App\Models\Vehicle;

trait ValidatesVehicleData
{
    public static function vehicleRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_odometer' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public static function vehicleMessages(): array
    {
        return [
            'name.required' => __('Nama kendaraan wajib diisi.'),
            'current_odometer.required' => __('Odometer wajib diisi.'),
        ];
    }

    public static function checkOdometerNotLess(mixed $validator, array $data, Vehicle $vehicle): void
    {
        if (! empty($data['current_odometer']) && (int) $data['current_odometer'] < (int) $vehicle->current_odometer) {
            $validator->errors()->add('current_odometer', __('Odometer tidak boleh lebih kecil dari sebelumnya (:value km).', ['value' => number_format($vehicle->current_odometer, 0, ',', '.')]));
        }
    }
}
