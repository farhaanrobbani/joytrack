<?php

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $document_type = '';
    public string $vehicle_id = '';
    public string $expiry_date = '';
    public string $reminder_days = '7';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->document_type = Document::TYPES[0];
    }

    public function save(): void
    {
        $data = $this->formData();

        $validator = Validator::make($data, StoreDocumentRequest::documentRules());
        $validator->after(fn ($validator) => StoreDocumentRequest::checkDocumentData($validator, auth()->id(), $data));
        $validator->validate();

        $data['user_id'] = auth()->id();
        Document::create($data);

        session()->flash('status', __('Dokumen berhasil dibuat.'));
        $this->redirect(route('documents.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'document_type' => $this->document_type,
            'vehicle_id' => $this->vehicle_id !== '' ? $this->vehicle_id : null,
            'expiry_date' => $this->expiry_date,
            'reminder_days' => $this->reminder_days,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'is_active' => $this->is_active,
        ];
    }

    public function render()
    {
        return $this->view([
            'vehicles' => Vehicle::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'types' => Document::TYPE_LABELS,
        ]);
    }
};
?>

<div>
    <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Dokumen')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="{{ __('STNK Mobil') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="document_type" :value="__('Jenis Dokumen')" />
                    <select id="document_type" wire:model="document_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        @foreach($types as $value => $label)
                            <option wire:key="type-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="vehicle_id" :value="__('Kendaraan (opsional)')" />
                    <select id="vehicle_id" wire:model="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="">{{ __('Tanpa kendaraan') }}</option>
                        @foreach($vehicles as $vehicle)
                            <option wire:key="vehicle-{{ $vehicle->id }}" value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->license_plate ?? '-' }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Hubungkan ke kendaraan untuk dokumen seperti STNK atau pajak.') }}</p>
                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="expiry_date" :value="__('Tanggal Kadaluarsa')" />
                    <x-text-input id="expiry_date" wire:model="expiry_date" type="date" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="reminder_days" :value="__('Ingatkan Sebelum (hari)')" />
                    <x-text-input id="reminder_days" wire:model="reminder_days" type="number" min="1" max="90" class="mt-1 block w-full" required />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Pengingat muncul di dashboard saat tanggal kadaluarsa kurang dari :days hari.', ['days' => 7]) }}</p>
                    <x-input-error :messages="$errors->get('reminder_days')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded-sm border-gray-300 text-emerald-600 shadow-xs focus:ring-emerald-500" />
                    <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                </div>
                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                <div class="flex gap-3">
                    <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
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
