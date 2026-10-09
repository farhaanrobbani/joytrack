<?php

use App\Http\Requests\StoreFuelRecordRequest;
use App\Models\Account;
use App\Models\Vehicle;
use App\Services\FuelRecordService;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $vehicle_id = '';
    public string $fuel_date = '';
    public string $odometer = '';
    public string $fuel_type = '';
    public string $liters = '';
    public string $price_per_liter = '';
    public string $total_cost = '';
    public string $station = '';
    public string $account_id = '';
    public bool $create_transaction = false;
    public string $notes = '';
    public string $total_hint = '';

    public function mount(?string $selectedVehicle = null): void
    {
        $this->fuel_date = now()->format('Y-m-d');
        if ($selectedVehicle !== null && $selectedVehicle !== '') {
            $this->vehicle_id = $selectedVehicle;
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['liters', 'price_per_liter'], true)) {
            $this->refreshTotalHint();
        }
    }

    private function refreshTotalHint(): void
    {
        if (is_numeric($this->liters) && is_numeric($this->price_per_liter) && (float) $this->liters > 0 && (float) $this->price_per_liter > 0) {
            $total = (float) $this->liters * (float) $this->price_per_liter;
            $this->total_hint = $this->liters . ' × ' . $this->price_per_liter . ' = ' . number_format($total, 2, '.', '');
        } else {
            $this->total_hint = '';
        }
    }

    public function save(): void
    {
        $data = $this->formData();

        $validator = Validator::make($data, StoreFuelRecordRequest::fuelRecordRules(true));
        $validator->after(fn ($validator) => StoreFuelRecordRequest::checkFuelRecordData($validator, auth()->id(), $data, checkOdometer: true, checkCreateTransaction: true));
        $validator->validate();

        $data['user_id'] = auth()->id();
        app(FuelRecordService::class)->create($data);

        session()->flash('status', __('Catatan BBM berhasil dibuat.'));
        $this->redirect(route('fuel-records.index'));
    }

    private function formData(): array
    {
        return [
            'vehicle_id' => $this->vehicle_id,
            'fuel_date' => $this->fuel_date,
            'odometer' => $this->odometer,
            'fuel_type' => $this->fuel_type !== '' ? $this->fuel_type : null,
            'liters' => $this->liters,
            'price_per_liter' => $this->price_per_liter,
            'total_cost' => $this->total_cost,
            'station' => $this->station !== '' ? $this->station : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'account_id' => $this->account_id !== '' ? $this->account_id : null,
            'create_transaction' => $this->create_transaction,
        ];
    }

    public function render()
    {
        return $this->view([
            'vehicles' => Vehicle::where('user_id', auth()->id())->active()->orderBy('name')->get(),
            'accounts' => Account::where('user_id', auth()->id())->active()->orderBy('name')->get(),
        ]);
    }
};
?>

<div>
    <div class="bg-white shadow sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="vehicle_id" :value="__('Kendaraan *')" />
                    <select id="vehicle_id" wire:model="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                        <option value="">{{ __('Pilih Kendaraan') }}</option>
                        @foreach($vehicles as $v)<option wire:key="v-{{ $v->id }}" value="{{ $v->id }}">{{ $v->name }} ({{ number_format($v->current_odometer, 0, ',', '.') }} km)</option>@endforeach
                    </select>
                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fuel_date" :value="__('Tanggal *')" />
                    <x-text-input id="fuel_date" wire:model="fuel_date" type="date" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('fuel_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="odometer" :value="__('Odometer (km) *')" />
                    <x-text-input id="odometer" wire:model="odometer" type="number" min="0" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fuel_type" :value="__('Jenis BBM')" />
                    <x-text-input id="fuel_type" wire:model="fuel_type" type="text" class="mt-1 block w-full" placeholder="Pertalite" />
                </div>
                <div>
                    <x-input-label for="liters" :value="__('Liter *')" />
                    <x-text-input id="liters" wire:model.live.debounce.300ms="liters" type="number" step="0.01" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('liters')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="price_per_liter" :value="__('Harga/Liter *')" />
                    <x-text-input id="price_per_liter" wire:model.live.debounce.300ms="price_per_liter" type="number" step="0.01" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('price_per_liter')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="total_cost" :value="__('Total Biaya *')" />
                    <x-text-input id="total_cost" wire:model="total_cost" type="number" step="0.01" class="mt-1 block w-full" required :placeholder="$total_hint !== '' ? substr($total_hint, strpos($total_hint, '=') + 2) : ''" />
                    <p class="mt-1 text-xs text-gray-500">{{ $total_hint !== '' ? __('Hitung: :calc', ['calc' => $total_hint]) : '' }}</p>
                    <x-input-error :messages="$errors->get('total_cost')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="station" :value="__('SPBU/Stasiun')" />
                    <x-text-input id="station" wire:model="station" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="account_id" :value="__('Akun Pembayaran')" />
                    <select id="account_id" wire:model="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="">{{ __('Tanpa transaksi keuangan') }}</option>
                        @foreach($accounts as $acc)<option wire:key="acc-{{ $acc->id }}" value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>@endforeach
                    </select>
                    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                </div>
                <div class="flex items-center">
                    <input id="create_transaction" wire:model="create_transaction" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <x-input-label for="create_transaction" :value="__('Buat transaksi pengeluaran')" class="ms-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('fuel-records.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
