<?php

namespace App\Http\Requests;

use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'renewal_cycle' => ['required', 'string', 'in:'.implode(',', array_keys(Subscription::CYCLES))],
            'next_renewal_date' => ['required', 'date'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:90'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
