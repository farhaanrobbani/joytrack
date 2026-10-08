<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Berlangganan') }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('subscriptions.store') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Nama Berlangganan')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="{{ __('Netflix') }}" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" :value="__('Nominal (opsional)')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount')" />
                        <p class="mt-1 text-sm text-gray-500">{{ __('Biaya per periode, hanya informatif.') }}</p>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="renewal_cycle" :value="__('Siklus Perpanjangan')" />
                        <select id="renewal_cycle" name="renewal_cycle" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            @foreach(\App\Models\Subscription::CYCLE_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('renewal_cycle', 'monthly') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Dipakai tombol Perpanjang untuk menghitung tanggal berikutnya.') }}</p>
                        <x-input-error :messages="$errors->get('renewal_cycle')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="next_renewal_date" :value="__('Tanggal Perpanjangan Berikutnya')" />
                        <x-text-input id="next_renewal_date" name="next_renewal_date" type="date" class="mt-1 block w-full" :value="old('next_renewal_date')" required />
                        <x-input-error :messages="$errors->get('next_renewal_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reminder_days" :value="__('Ingatkan Sebelum (hari)')" />
                        <x-text-input id="reminder_days" name="reminder_days" type="number" min="1" max="90" class="mt-1 block w-full" :value="old('reminder_days', 7)" required />
                        <x-input-error :messages="$errors->get('reminder_days')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" {{ old('is_active', true) ? 'checked' : '' }}>
                        <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                    </div>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                    <div class="flex gap-3">
                        <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
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
