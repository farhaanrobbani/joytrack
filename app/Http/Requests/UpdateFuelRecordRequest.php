<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFuelRecordData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFuelRecordRequest extends FormRequest
{
    use ValidatesFuelRecordData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->fuel_record);
    }

    public function rules(): array
    {
        return static::fuelRecordRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkFuelRecordData($validator, $this->user()->id, $this->all()));
    }
}
