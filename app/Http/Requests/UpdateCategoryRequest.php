<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCategoryData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    use ValidatesCategoryData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->category);
    }

    public function rules(): array
    {
        return static::categoryRules();
    }

    public function messages(): array
    {
        return static::categoryMessages();
    }
}
