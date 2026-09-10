<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Kendaraan') }}</h2></x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" :value="__('Nama Kendaraan *')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $vehicle->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="license_plate" :value="__('Nomor Polisi')" />
                        <x-text-input id="license_plate" name="license_plate" type="text" class="mt-1 block w-full" :value="old('license_plate', $vehicle->license_plate)" />
                    </div>
                    <div>
                        <x-input-label for="vehicle_type" :value="__('Jenis Kendaraan')" />
                        <select id="vehicle_type" name="vehicle_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Pilih') }}</option>
                            <option value="motor" @selected(old('vehicle_type', $vehicle->vehicle_type)==='motor')>Motor</option>
                            <option value="mobil" @selected(old('vehicle_type', $vehicle->vehicle_type)==='mobil')>Mobil</option>
                            <option value="other" @selected(old('vehicle_type', $vehicle->vehicle_type)==='other')>{{ __('Lainnya') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="brand" :value="__('Merek')" />
                        <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" :value="old('brand', $vehicle->brand)" />
                    </div>
                    <div>
                        <x-input-label for="model" :value="__('Model')" />
                        <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" :value="old('model', $vehicle->model)" />
                    </div>
                    <div>
                        <x-input-label for="year" :value="__('Tahun')" />
                        <x-text-input id="year" name="year" type="number" class="mt-1 block w-full" :value="old('year', $vehicle->year)" />
                        <x-input-error :messages="$errors->get('year')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="color" :value="__('Warna')" />
                        <x-text-input id="color" name="color" type="text" class="mt-1 block w-full" :value="old('color', $vehicle->color)" />
                    </div>
                    <div>
                        <x-input-label for="current_odometer" :value="__('Odometer Saat Ini (km) *')" />
                        <x-text-input id="current_odometer" name="current_odometer" type="number" min="0" class="mt-1 block w-full" :value="old('current_odometer', $vehicle->current_odometer)" required />
                        <x-input-error :messages="$errors->get('current_odometer')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Tidak boleh lebih kecil dari :value km', ['value' => number_format($vehicle->current_odometer,0,',','.')]) }}</p>
                    </div>
                    <div>
                        <x-input-label for="purchase_date" :value="__('Tanggal Pembelian')" />
                        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full" :value="old('purchase_date', $vehicle->purchase_date?->format('Y-m-d'))" />
                    </div>
                    <div>
                        <x-input-label for="purchase_price" :value="__('Harga Pembelian')" />
                        <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" class="mt-1 block w-full" :value="old('purchase_price', $vehicle->purchase_price)" />
                    </div>
                    <div>
                        <x-input-label for="chassis_number" :value="__('Nomor Rangka')" />
                        <x-text-input id="chassis_number" name="chassis_number" type="text" class="mt-1 block w-full" :value="old('chassis_number', $vehicle->chassis_number)" />
                    </div>
                    <div>
                        <x-input-label for="engine_number" :value="__('Nomor Mesin')" />
                        <x-text-input id="engine_number" name="engine_number" type="text" class="mt-1 block w-full" :value="old('engine_number', $vehicle->engine_number)" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes', $vehicle->notes) }}</textarea>
                    </div>
                    <div class="sm:col-span-2 flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" {{ old('is_active', $vehicle->is_active) ? 'checked' : '' }}>
                        <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <a href="{{ route('vehicles.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                    <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
