<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTransactionData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    use ValidatesTransactionData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->transaction);
    }

    public function rules(): array
    {
        return static::transactionRules($this->all());
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkTransactionOwnership($validator, $this->user()->id, $this->all()));
    }

    public function messages(): array
    {
        return static::transactionMessages();
    }
}
