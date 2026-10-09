<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesVehicleData;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    use ValidatesVehicleData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::vehicleRules();
    }

    public function messages(): array
    {
        return static::vehicleMessages();
    }
}
