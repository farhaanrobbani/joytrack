<?php

use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Vehicle $vehicle;

    public string $name = '';
    public string $license_plate = '';
    public string $vehicle_type = '';
    public string $brand = '';
    public string $model = '';
    public string $year = '';
    public string $color = '';
    public string $current_odometer = '0';
    public string $purchase_date = '';
    public string $purchase_price = '';
    public string $chassis_number = '';
    public string $engine_number = '';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(Vehicle $vehicle): void
    {
        Gate::authorize('update', $vehicle);

        $this->vehicle = $vehicle;
        $this->name = $vehicle->name;
        $this->license_plate = $vehicle->license_plate ?? '';
        $this->vehicle_type = $vehicle->vehicle_type ?? '';
        $this->brand = $vehicle->brand ?? '';
        $this->model = $vehicle->model ?? '';
        $this->year = $vehicle->year !== null ? (string) $vehicle->year : '';
        $this->color = $vehicle->color ?? '';
        $this->current_odometer = (string) $vehicle->current_odometer;
        $this->purchase_date = $vehicle->purchase_date?->format('Y-m-d') ?? '';
        $this->purchase_price = $vehicle->purchase_price !== null ? (string) $vehicle->purchase_price : '';
        $this->chassis_number = $vehicle->chassis_number ?? '';
        $this->engine_number = $vehicle->engine_number ?? '';
        $this->notes = $vehicle->notes ?? '';
        $this->is_active = (bool) $vehicle->is_active;
    }

    public function save(): void
    {
        Gate::authorize('update', $this->vehicle);

        $data = $this->formData();

        $validator = Validator::make($data, UpdateVehicleRequest::vehicleRules(), UpdateVehicleRequest::vehicleMessages());
        $validator->after(fn ($validator) => UpdateVehicleRequest::checkOdometerNotLess($validator, $data, $this->vehicle));
        $validator->validate();

        $this->vehicle->update($data);

        session()->flash('status', __('Kendaraan berhasil diperbarui.'));
        $this->redirect(route('vehicles.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'license_plate' => $this->license_plate !== '' ? $this->license_plate : null,
            'vehicle_type' => $this->vehicle_type !== '' ? $this->vehicle_type : null,
            'brand' => $this->brand !== '' ? $this->brand : null,
            'model' => $this->model !== '' ? $this->model : null,
            'year' => $this->year !== '' ? $this->year : null,
            'color' => $this->color !== '' ? $this->color : null,
            'current_odometer' => $this->current_odometer,
            'purchase_date' => $this->purchase_date !== '' ? $this->purchase_date : null,
            'purchase_price' => $this->purchase_price !== '' ? $this->purchase_price : null,
            'chassis_number' => $this->chassis_number !== '' ? $this->chassis_number : null,
            'engine_number' => $this->engine_number !== '' ? $this->engine_number : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'is_active' => $this->is_active,
        ];
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<div>
    <div class="bg-white shadow sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <x-input-label for="name" :value="__('Nama Kendaraan *')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="license_plate" :value="__('Nomor Polisi')" />
                    <x-text-input id="license_plate" wire:model="license_plate" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('license_plate')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="vehicle_type" :value="__('Jenis Kendaraan')" />
                    <select id="vehicle_type" wire:model="vehicle_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="">{{ __('Pilih') }}</option>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="other">{{ __('Lainnya') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="brand" :value="__('Merek')" />
                    <x-text-input id="brand" wire:model="brand" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="model" :value="__('Model')" />
                    <x-text-input id="model" wire:model="model" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="year" :value="__('Tahun')" />
                    <x-text-input id="year" wire:model="year" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('year')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="color" :value="__('Warna')" />
                    <x-text-input id="color" wire:model="color" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="current_odometer" :value="__('Odometer Saat Ini (km) *')" />
                    <x-text-input id="current_odometer" wire:model="current_odometer" type="number" min="0" class="mt-1 block w-full" required />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Tidak boleh lebih kecil dari :value km', ['value' => number_format($this->vehicle->current_odometer, 0, ',', '.')]) }}</p>
                    <x-input-error :messages="$errors->get('current_odometer')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="purchase_date" :value="__('Tanggal Pembelian')" />
                    <x-text-input id="purchase_date" wire:model="purchase_date" type="date" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="purchase_price" :value="__('Harga Pembelian')" />
                    <x-text-input id="purchase_price" wire:model="purchase_price" type="number" step="0.01" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="chassis_number" :value="__('Nomor Rangka')" />
                    <x-text-input id="chassis_number" wire:model="chassis_number" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="engine_number" :value="__('Nomor Mesin')" />
                    <x-text-input id="engine_number" wire:model="engine_number" type="text" class="mt-1 block w-full" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                </div>
                <div class="sm:col-span-2 flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
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
