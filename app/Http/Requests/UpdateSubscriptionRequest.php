<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSubscriptionData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    use ValidatesSubscriptionData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::subscriptionRules();
    }
}
