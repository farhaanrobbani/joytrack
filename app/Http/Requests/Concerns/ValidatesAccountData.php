<?php

namespace App\Http\Requests\Concerns;

trait ValidatesAccountData
{
    public static function accountRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,cash,ewallet,savings,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public static function accountMessages(): array
    {
        return [
            'name.required' => __('Nama akun wajib diisi.'),
            'type.required' => __('Jenis akun wajib dipilih.'),
            'type.in' => __('Jenis akun tidak valid.'),
        ];
    }

    public static function storeAccountRules(): array
    {
        return [
            ...static::accountRules(),
            'initial_balance' => ['required', 'numeric', 'min:0'],
        ];
    }

    public static function storeAccountMessages(): array
    {
        return [
            ...static::accountMessages(),
            'initial_balance.required' => __('Saldo awal wajib diisi.'),
            'initial_balance.numeric' => __('Saldo awal harus berupa angka.'),
            'initial_balance.min' => __('Saldo awal tidak boleh negatif.'),
        ];
    }
}
