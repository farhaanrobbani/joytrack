<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAccountData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    use ValidatesAccountData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->account);
    }

    public function rules(): array
    {
        return static::accountRules();
    }

    public function messages(): array
    {
        return static::accountMessages();
    }
}
