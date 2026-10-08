<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'in:'.implode(',', Document::TYPES)],
            'expiry_date' => ['required', 'date'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:90'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;
            if ($this->filled('vehicle_id') && ! Vehicle::where('id', $this->vehicle_id)->where('user_id', $userId)->exists()) {
                $validator->errors()->add('vehicle_id', __('Kendaraan tidak valid.'));
            }
        });
    }
}
