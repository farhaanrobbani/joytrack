<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDocumentData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    use ValidatesDocumentData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::documentRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkDocumentData($validator, $this->user()->id, $this->all()));
    }
}
