<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Dokumen') }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('documents.update', $document) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Nama Dokumen')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $document->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="document_type" :value="__('Jenis Dokumen')" />
                        <select id="document_type" name="document_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type', $document->document_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="vehicle_id" :value="__('Kendaraan (opsional)')" />
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Tanpa kendaraan') }}</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $document->vehicle_id) == $vehicle->id)>{{ $vehicle->name }} ({{ $vehicle->license_plate ?? '-' }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="expiry_date" :value="__('Tanggal Kadaluarsa')" />
                        <x-text-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full" :value="old('expiry_date', $document->expiry_date->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reminder_days" :value="__('Ingatkan Sebelum (hari)')" />
                        <x-text-input id="reminder_days" name="reminder_days" type="number" min="1" max="90" class="mt-1 block w-full" :value="old('reminder_days', $document->reminder_days)" required />
                        <x-input-error :messages="$errors->get('reminder_days')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes', $document->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" {{ old('is_active', $document->is_active) ? 'checked' : '' }}>
                        <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                    </div>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                    <div class="flex gap-3">
                        <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
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
