<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Servis') }}</h2></x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('service-records.update', $serviceRecord) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="vehicle_id" :value="__('Kendaraan *')" />
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected(old('vehicle_id', $serviceRecord->vehicle_id)==$v->id)>{{ $v->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="service_date" :value="__('Tanggal Servis *')" />
                        <x-text-input id="service_date" name="service_date" type="date" class="mt-1 block w-full" :value="old('service_date', $serviceRecord->service_date->format('Y-m-d'))" required />
                    </div>
                    <div>
                        <x-input-label for="odometer" :value="__('Odometer *')" />
                        <x-text-input id="odometer" name="odometer" type="number" min="0" class="mt-1 block w-full" :value="old('odometer', $serviceRecord->odometer)" required />
                        <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="service_type" :value="__('Jenis Servis *')" />
                        <select id="service_type" name="service_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                            @foreach($serviceTypes as $t)<option value="{{ $t }}" @selected(old('service_type', $serviceRecord->service_type)==$t)>{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="workshop" :value="__('Bengkel')" />
                        <x-text-input id="workshop" name="workshop" type="text" class="mt-1 block w-full" :value="old('workshop', $serviceRecord->workshop)" />
                    </div>
                    <div>
                        <x-input-label for="labor_cost" :value="__('Biaya Jasa *')" />
                        <x-text-input id="labor_cost" name="labor_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('labor_cost', $serviceRecord->labor_cost)" required />
                    </div>
                    <div>
                        <x-input-label for="parts_cost" :value="__('Biaya Sparepart *')" />
                        <x-text-input id="parts_cost" name="parts_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('parts_cost', $serviceRecord->parts_cost)" required />
                    </div>
                    <div>
                        <x-input-label for="total_cost" :value="__('Total Biaya *')" />
                        <x-text-input id="total_cost" name="total_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('total_cost', $serviceRecord->total_cost)" required />
                        <x-input-error :messages="$errors->get('total_cost')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="next_service_date" :value="__('Servis Berikutnya (Tanggal)')" />
                        <x-text-input id="next_service_date" name="next_service_date" type="date" class="mt-1 block w-full" :value="old('next_service_date', $serviceRecord->next_service_date?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="next_service_odometer" :value="__('Servis Berikutnya (KM)')" />
                        <x-text-input id="next_service_odometer" name="next_service_odometer" type="number" min="0" class="mt-1 block w-full" :value="old('next_service_odometer', $serviceRecord->next_service_odometer)" />
                        <x-input-error :messages="$errors->get('next_service_odometer')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="account_id" :value="__('Akun Pembayaran')" />
                        <select id="account_id" name="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Tanpa transaksi keuangan') }}</option>
                            @foreach($accounts as $acc)<option value="{{ $acc->id }}" @selected(old('account_id', $serviceRecord->account_id)==$acc->id)>{{ $acc->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes', $serviceRecord->notes) }}</textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <a href="{{ route('service-records.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold">{{ __('Batal') }}</a>
                    <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
