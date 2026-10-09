<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesServiceRecordData;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRecordRequest extends FormRequest
{
    use ValidatesServiceRecordData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::serviceRecordRules(true);
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkServiceRecordData($validator, $this->user()->id, $this->all(), checkCreateTransaction: true));
    }
}
