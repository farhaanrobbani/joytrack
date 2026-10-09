<?php

use App\Models\ServiceRecord;
use App\Models\Vehicle;
use App\Services\ServiceReminderService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'vehicle_id')]
    public string $vehicleId = '';

    #[Url(as: 'service_type')]
    public string $serviceType = '';

    #[Url(as: 'workshop')]
    public string $workshop = '';

    #[Url(as: 'date_from')]
    public string $dateFrom = '';

    #[Url(as: 'date_to')]
    public string $dateTo = '';

    public int $upcoming = 0;

    public function mount(): void
    {
        $this->upcoming = app(ServiceReminderService::class)->getReminders(auth()->id())->count();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['vehicleId', 'serviceType', 'workshop', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['vehicleId', 'serviceType', 'workshop', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $query = ServiceRecord::where('user_id', auth()->id())
            ->with(['vehicle', 'account', 'transaction'])
            ->orderByDesc('service_date')
            ->orderByDesc('id');

        if ($this->vehicleId !== '') {
            $query->where('vehicle_id', $this->vehicleId);
        }
        if ($this->serviceType !== '') {
            $query->where('service_type', 'like', '%' . $this->serviceType . '%');
        }
        if ($this->workshop !== '') {
            $query->where('workshop', 'like', '%' . $this->workshop . '%');
        }
        if ($this->dateFrom !== '') {
            $query->where('service_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->where('service_date', '<=', $this->dateTo);
        }

        return $this->view([
            'records' => $query->paginate(15),
            'vehicles' => Vehicle::where('user_id', auth()->id())->orderBy('name')->get(),
        ]);
    }
};
?>

<div>
    @if($upcoming > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <p class="text-sm font-medium text-amber-800">{{ __('Ada :count servis dengan pengingat akan jatuh tempo.', ['count' => $upcoming]) }}</p>
        </div>
    @endif

    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <select wire:model.live="vehicleId" class="border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('Semua Kendaraan') }}</option>
                @foreach($vehicles as $v)<option wire:key="v-{{ $v->id }}" value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
            </select>
            <input type="text" wire:model.live.debounce.400ms="serviceType" placeholder="{{ __('Jenis servis') }}" class="border-gray-300 rounded-lg text-sm">
            <input type="text" wire:model.live.debounce.400ms="workshop" placeholder="{{ __('Bengkel') }}" class="border-gray-300 rounded-lg text-sm">
            <input type="date" wire:model.live="dateFrom" class="border-gray-300 rounded-lg text-sm">
            <input type="date" wire:model.live="dateTo" class="border-gray-300 rounded-lg text-sm">
            <div class="sm:col-span-5 flex gap-2">
                <button type="button" wire:click="resetFilters" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold">{{ __('Reset') }}</button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden" wire:loading.class="opacity-50">
        @if($records->isEmpty())
            <div class="p-8 text-center text-gray-500">{{ __('Belum ada catatan servis') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kendaraan') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jenis') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Bengkel') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Biaya') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Next Service') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($records as $r)
                            <tr wire:key="service-{{ $r->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $r->service_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->vehicle->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->service_type }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->workshop ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($r->total_cost, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($r->next_service_date) {{ $r->next_service_date->format('d M Y') }} @endif
                                    @if($r->next_service_date && $r->next_service_odometer) • @endif
                                    @if($r->next_service_odometer) {{ number_format($r->next_service_odometer, 0, ',', '.') }} km @endif
                                    @if(!$r->next_service_date && !$r->next_service_odometer) - @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('service-records.show', $r) }}" class="text-emerald-600 hover:text-emerald-700 mr-2">{{ __('Lihat') }}</a>
                                    <a href="{{ route('service-records.edit', $r) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('service-records.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $records->links() }}</div>
        @endif
    </div>
</div>
