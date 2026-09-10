<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Akun') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Nama Akun')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('Jenis Akun')" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>{{ __('Bank') }}</option>
                            <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                            <option value="ewallet" {{ old('type') === 'ewallet' ? 'selected' : '' }}>{{ __('E-wallet') }}</option>
                            <option value="savings" {{ old('type') === 'savings' ? 'selected' : '' }}>{{ __('Savings') }}</option>
                            <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="initial_balance" :value="__('Saldo Awal')" />
                        <x-text-input id="initial_balance" name="initial_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('initial_balance', 0)" required />
                        <p class="mt-1 text-sm text-gray-500">{{ __('Saldo saat pertama kali mencatat akun ini.') }}</p>
                        <x-input-error :messages="$errors->get('initial_balance')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" {{ old('is_active', true) ? 'checked' : '' }}>
                        <x-input-label for="is_active" :value="__('Akun Aktif')" class="ms-2" />
                    </div>

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
