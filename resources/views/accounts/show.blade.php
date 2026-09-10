<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Akun') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold text-sm hover:bg-emerald-700 transition-colors">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition-colors">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-4">{{ __('Informasi Akun') }}</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Nama') }}</p>
                    <p class="text-lg font-medium text-gray-900">{{ $account->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Jenis') }}</p>
                    <p class="text-lg font-medium text-gray-900">{{ $account->type_label }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $account->is_active ? __('Aktif') : __('Nonaktif') }}
                    </span>
                </div>
                @if ($account->description)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Deskripsi') }}</p>
                        <p class="text-gray-900">{{ $account->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-4">{{ __('Saldo') }}</h3>
            <div class="space-y-4">
                <div class="p-4 bg-emerald-50 rounded-lg">
                    <p class="text-sm text-emerald-600">{{ __('Saldo Saat Ini') }}</p>
                    <p class="text-3xl font-bold text-emerald-700">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">{{ __('Saldo Awal') }}</p>
                    <p class="text-xl font-semibold text-gray-700">Rp {{ number_format($account->initial_balance, 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-600">{{ __('Selisih') }}</p>
                    <p class="text-xl font-semibold {{ $account->current_balance > $account->initial_balance ? 'text-emerald-600' : ($account->current_balance < $account->initial_balance ? 'text-red-600' : 'text-gray-700') }}">
                        {{ $account->current_balance > $account->initial_balance ? '+' : '' }}Rp {{ number_format($account->current_balance - $account->initial_balance, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
