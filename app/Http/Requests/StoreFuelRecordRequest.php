<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFuelRecordData;
use Illuminate\Foundation\Http\FormRequest;

class StoreFuelRecordRequest extends FormRequest
{
    use ValidatesFuelRecordData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::fuelRecordRules(true);
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkFuelRecordData($validator, $this->user()->id, $this->all(), checkOdometer: true, checkCreateTransaction: true));
    }
}
