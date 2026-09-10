<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Servis') }}</h2></x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('service-records.store') }}" x-data="{ labor: '{{ old('labor_cost', 0) }}', parts: '{{ old('parts_cost', 0) }}' }">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="vehicle_id" :value="__('Kendaraan *')" />
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                            <option value="">{{ __('Pilih Kendaraan') }}</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected(old('vehicle_id')==$v->id)>{{ $v->name }} ({{ number_format($v->current_odometer,0,',','.') }} km)</option>@endforeach
                        </select>
                        <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="service_date" :value="__('Tanggal Servis *')" />
                        <x-text-input id="service_date" name="service_date" type="date" class="mt-1 block w-full" :value="old('service_date', now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('service_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="odometer" :value="__('Odometer (km) *')" />
                        <x-text-input id="odometer" name="odometer" type="number" min="0" class="mt-1 block w-full" :value="old('odometer')" required />
                        <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="service_type" :value="__('Jenis Servis *')" />
                        <select id="service_type" name="service_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                            <option value="">{{ __('Pilih') }}</option>
                            @foreach($serviceTypes as $t)<option value="{{ $t }}" @selected(old('service_type')==$t)>{{ $t }}</option>@endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="workshop" :value="__('Bengkel')" />
                        <x-text-input id="workshop" name="workshop" type="text" class="mt-1 block w-full" :value="old('workshop')" placeholder="Bengkel ABC" />
                    </div>
                    <div>
                        <x-input-label for="labor_cost" :value="__('Biaya Jasa *')" />
                        <x-text-input id="labor_cost" name="labor_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model="labor" :value="old('labor_cost', 0)" required />
                        <x-input-error :messages="$errors->get('labor_cost')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="parts_cost" :value="__('Biaya Sparepart *')" />
                        <x-text-input id="parts_cost" name="parts_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model="parts" :value="old('parts_cost', 0)" required />
                        <x-input-error :messages="$errors->get('parts_cost')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="total_cost" :value="__('Total Biaya *')" />
                        <x-text-input id="total_cost" name="total_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('total_cost')" required x-bind:placeholder="labor && parts ? (parseFloat(labor)+parseFloat(parts)).toFixed(2) : ''" />
                        <p class="mt-1 text-xs text-gray-500" x-text="labor && parts ? labor + ' + ' + parts + ' = ' + (parseFloat(labor)+parseFloat(parts)).toFixed(2) : ''"></p>
                        <x-input-error :messages="$errors->get('total_cost')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="next_service_date" :value="__('Servis Berikutnya (Tanggal)')" />
                        <x-text-input id="next_service_date" name="next_service_date" type="date" class="mt-1 block w-full" :value="old('next_service_date')" />
                        <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="next_service_odometer" :value="__('Servis Berikutnya (KM)')" />
                        <x-text-input id="next_service_odometer" name="next_service_odometer" type="number" min="0" class="mt-1 block w-full" :value="old('next_service_odometer')" placeholder="50000" />
                        <x-input-error :messages="$errors->get('next_service_odometer')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="account_id" :value="__('Akun Pembayaran')" />
                        <select id="account_id" name="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Tanpa transaksi keuangan') }}</option>
                            @foreach($accounts as $acc)<option value="{{ $acc->id }}" @selected(old('account_id')==$acc->id)>{{ $acc->name }} (Rp {{ number_format($acc->current_balance,0,',','.') }})</option>@endforeach
                        </select>
                        <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                    </div>
                    <div class="flex items-center">
                        <input id="create_transaction" name="create_transaction" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" {{ old('create_transaction') ? 'checked' : '' }}>
                        <x-input-label for="create_transaction" :value="__('Buat transaksi pengeluaran')" class="ms-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <a href="{{ route('service-records.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                    <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
