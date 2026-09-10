<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,cash,ewallet,savings,other'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Nama akun wajib diisi.'),
            'type.required' => __('Jenis akun wajib dipilih.'),
            'type.in' => __('Jenis akun tidak valid.'),
            'initial_balance.required' => __('Saldo awal wajib diisi.'),
            'initial_balance.numeric' => __('Saldo awal harus berupa angka.'),
            'initial_balance.min' => __('Saldo awal tidak boleh negatif.'),
        ];
    }
}
