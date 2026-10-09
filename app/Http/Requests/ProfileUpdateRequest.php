<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesProfileData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ValidatesProfileData;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return static::profileRules($this->user()->id);
    }
}
