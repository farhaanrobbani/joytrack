<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesServiceRecordData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRecordRequest extends FormRequest
{
    use ValidatesServiceRecordData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->service_record);
    }

    public function rules(): array
    {
        return static::serviceRecordRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkServiceRecordData($validator, $this->user()->id, $this->all()));
    }
}
