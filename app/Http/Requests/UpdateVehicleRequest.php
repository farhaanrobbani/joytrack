<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesVehicleData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    use ValidatesVehicleData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->vehicle);
    }

    public function rules(): array
    {
        return static::vehicleRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkOdometerNotLess($validator, $this->all(), $this->route('vehicle')));
    }

    public function messages(): array
    {
        return static::vehicleMessages();
    }
}
