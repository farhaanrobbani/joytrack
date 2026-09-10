<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Akun') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('accounts.update', $account) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Nama Akun')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $account->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('Jenis Akun')" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="bank" {{ old('type', $account->type) === 'bank' ? 'selected' : '' }}>{{ __('Bank') }}</option>
                            <option value="cash" {{ old('type', $account->type) === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                            <option value="ewallet" {{ old('type', $account->type) === 'ewallet' ? 'selected' : '' }}>{{ __('E-wallet') }}</option>
                            <option value="savings" {{ old('type', $account->type) === 'savings' ? 'selected' : '' }}>{{ __('Savings') }}</option>
                            <option value="other" {{ old('type', $account->type) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label :value="__('Saldo Awal')" />
                        <p class="mt-1 text-gray-700 font-medium">Rp {{ number_format($account->initial_balance, 0, ',', '.') }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Saldo awal tidak bisa diubah.') }}</p>
                    </div>

                    <div>
                        <x-input-label :value="__('Saldo Saat Ini')" />
                        <p class="mt-1 text-gray-700 font-medium">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Saldo akan berubah otomatis sesuai transaksi.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('description', $account->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                        <x-input-label for="is_active" :value="__('Akun Aktif')" class="ms-2" />
                    </div>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                    <div class="flex gap-3">
                        <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button type="submit" class="ms-auto">
                            {{ __('Simpan') }}
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
