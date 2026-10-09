<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSubscriptionData;
use Illuminate\Foundation\Http\FormRequest;

class RenewSubscriptionRequest extends FormRequest
{
    use ValidatesSubscriptionData;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return static::renewRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(fn ($validator) => static::checkRenewData(
            $validator,
            $this->user()->id,
            $this->all() + ['create_transaction' => $this->boolean('create_transaction')],
            $this->route('subscription'),
        ));
    }
}
