<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAccountData;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    use ValidatesAccountData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::storeAccountRules();
    }

    public function messages(): array
    {
        return static::storeAccountMessages();
    }
}
