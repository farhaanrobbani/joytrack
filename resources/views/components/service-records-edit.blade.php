<?php

use App\Http\Requests\UpdateServiceRecordRequest;
use App\Models\Account;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use App\Services\ServiceRecordService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public const SERVICE_TYPES = ['Servis rutin', 'Ganti oli', 'Ganti filter oli', 'Ganti filter udara', 'Ganti busi', 'Ganti kampas rem', 'Ganti ban', 'Servis CVT', 'Servis mesin', 'Servis AC', 'Kelistrikan', 'Perbaikan', 'Sparepart', 'Lainnya'];

    #[Locked]
    public ServiceRecord $serviceRecord;

    public string $vehicle_id = '';
    public string $service_date = '';
    public string $odometer = '';
    public string $service_type = '';
    public string $workshop = '';
    public string $labor_cost = '0';
    public string $parts_cost = '0';
    public string $total_cost = '';
    public string $next_service_date = '';
    public string $next_service_odometer = '';
    public string $account_id = '';
    public string $notes = '';

    public function mount(ServiceRecord $serviceRecord): void
    {
        Gate::authorize('update', $serviceRecord);

        $this->serviceRecord = $serviceRecord;
        $this->vehicle_id = (string) $serviceRecord->vehicle_id;
        $this->service_date = $serviceRecord->service_date->format('Y-m-d');
        $this->odometer = (string) $serviceRecord->odometer;
        $this->service_type = $serviceRecord->service_type;
        $this->workshop = $serviceRecord->workshop ?? '';
        $this->labor_cost = (string) $serviceRecord->labor_cost;
        $this->parts_cost = (string) $serviceRecord->parts_cost;
        $this->total_cost = (string) $serviceRecord->total_cost;
        $this->next_service_date = $serviceRecord->next_service_date?->format('Y-m-d') ?? '';
        $this->next_service_odometer = $serviceRecord->next_service_odometer !== null ? (string) $serviceRecord->next_service_odometer : '';
        $this->account_id = $serviceRecord->account_id !== null ? (string) $serviceRecord->account_id : '';
        $this->notes = $serviceRecord->notes ?? '';
    }

    public function save(): void
    {
        Gate::authorize('update', $this->serviceRecord);

        $data = $this->formData();

        $validator = Validator::make($data, UpdateServiceRecordRequest::serviceRecordRules());
        $validator->after(fn ($validator) => UpdateServiceRecordRequest::checkServiceRecordData($validator, auth()->id(), $data));
        $validator->validate();

        app(ServiceRecordService::class)->update($this->serviceRecord, $data);

        session()->flash('status', __('Catatan servis berhasil diperbarui.'));
        $this->redirect(route('service-records.index'));
    }

    private function formData(): array
    {
        return [
            'vehicle_id' => $this->vehicle_id,
            'service_date' => $this->service_date,
            'odometer' => $this->odometer,
            'service_type' => $this->service_type,
            'workshop' => $this->workshop !== '' ? $this->workshop : null,
            'labor_cost' => $this->labor_cost,
            'parts_cost' => $this->parts_cost,
            'total_cost' => $this->total_cost,
            'next_service_date' => $this->next_service_date !== '' ? $this->next_service_date : null,
            'next_service_odometer' => $this->next_service_odometer !== '' ? $this->next_service_odometer : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'account_id' => $this->account_id !== '' ? $this->account_id : null,
        ];
    }

    public function render()
    {
        return $this->view([
            'vehicles' => Vehicle::where('user_id', auth()->id())->orderBy('name')->get(),
            'accounts' => Account::where('user_id', auth()->id())->orderBy('name')->get(),
            'serviceTypes' => self::SERVICE_TYPES,
        ]);
    }
};
?>

<div>
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="vehicle_id" :value="__('Kendaraan *')" />
                    <select id="vehicle_id" wire:model="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                        @foreach($vehicles as $v)<option wire:key="v-{{ $v->id }}" value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
                    </select>
                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="service_date" :value="__('Tanggal Servis *')" />
                    <x-text-input id="service_date" wire:model="service_date" type="date" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('service_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="odometer" :value="__('Odometer *')" />
                    <x-text-input id="odometer" wire:model="odometer" type="number" min="0" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="service_type" :value="__('Jenis Servis *')" />
                    <select id="service_type" wire:model="service_type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                        @foreach($serviceTypes as $t)<option wire:key="st-{{ $t }}" value="{{ $t }}">{{ $t }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="workshop" :value="__('Bengkel')" />
                    <x-text-input id="workshop" wire:model="workshop" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="labor_cost" :value="__('Biaya Jasa *')" />
                    <x-text-input id="labor_cost" wire:model="labor_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('labor_cost')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="parts_cost" :value="__('Biaya Sparepart *')" />
                    <x-text-input id="parts_cost" wire:model="parts_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('parts_cost')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="total_cost" :value="__('Total Biaya *')" />
                    <x-text-input id="total_cost" wire:model="total_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('total_cost')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="next_service_date" :value="__('Servis Berikutnya (Tanggal)')" />
                    <x-text-input id="next_service_date" wire:model="next_service_date" type="date" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="next_service_odometer" :value="__('Servis Berikutnya (KM)')" />
                    <x-text-input id="next_service_odometer" wire:model="next_service_odometer" type="number" min="0" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('next_service_odometer')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="account_id" :value="__('Akun Pembayaran')" />
                    <select id="account_id" wire:model="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="">{{ __('Tanpa transaksi keuangan') }}</option>
                        @foreach($accounts as $acc)<option wire:key="acc-{{ $acc->id }}" value="{{ $acc->id }}">{{ $acc->name }}</option>@endforeach
                    </select>
                    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('service-records.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
