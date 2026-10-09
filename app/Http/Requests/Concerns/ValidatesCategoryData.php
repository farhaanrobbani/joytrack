<?php

namespace App\Http\Requests\Concerns;

trait ValidatesCategoryData
{
    public static function categoryRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public static function categoryMessages(): array
    {
        return [
            'name.required' => __('Nama kategori wajib diisi.'),
            'type.required' => __('Jenis kategori wajib dipilih.'),
            'type.in' => __('Jenis kategori tidak valid.'),
        ];
    }
}
