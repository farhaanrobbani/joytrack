<?php

namespace App\Http\Requests\Concerns;

use App\Models\Document;
use App\Models\Vehicle;

trait ValidatesDocumentData
{
    public static function documentRules(): array
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

    public static function checkDocumentData(mixed $validator, int $userId, array $data): void
    {
        if (! empty($data['vehicle_id']) && ! Vehicle::where('id', $data['vehicle_id'])->where('user_id', $userId)->exists()) {
            $validator->errors()->add('vehicle_id', __('Kendaraan tidak valid.'));
        }
    }
}
