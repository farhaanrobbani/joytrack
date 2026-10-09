<?php

use App\Http\Traits\ResolvesReportPreset;
use App\Models\Vehicle;
use App\Services\ReportService;
use Livewire\Component;

new class extends Component
{
    use ResolvesReportPreset;

    public string $preset = 'month';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public $vehicleId = null;

    public function mount(string $preset = 'month', ?string $startDate = null, ?string $endDate = null, $vehicleId = null): void
    {
        $this->preset = $preset;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->vehicleId = $vehicleId;
    }

    public function render()
    {
        [$start, $end] = self::resolvePresetValues($this->preset, $this->startDate, $this->endDate);

        $service = app(ReportService::class);
        $userId = auth()->id();
        $vehicleId = $this->vehicleId ? (int) $this->vehicleId : null;

        $data = match ('fuel') {
            'finance' => $service->finance($userId, $start, $end),
            'vehicle' => $service->vehicle($userId, $start, $end, $vehicleId),
            'fuel' => $service->fuel($userId, $start, $end, $vehicleId),
            'service' => $service->service($userId, $start, $end, $vehicleId),
        };

        $extra = ['preset' => $this->preset, 'selectedVehicle' => $this->vehicleId];
        if ('fuel' !== 'finance') {
            $extra['vehicles'] = Vehicle::where('user_id', $userId)->orderBy('name')->get();
        }

        return $this->view(array_merge($data, $extra));
    }
};
?>

<div>
<div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
    <form method="GET" action="{{ route('reports.fuel') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <x-input-label :value="__('Periode')" />
            <select name="preset" onchange="this.form.submit()" class="mt-1 border-gray-300 rounded-lg text-sm">
                <option value="today" @selected($preset==='today')>{{ __('Hari ini') }}</option>
                <option value="week" @selected($preset==='week')>{{ __('Minggu ini') }}</option>
                <option value="month" @selected($preset==='month')>{{ __('Bulan ini') }}</option>
                <option value="year" @selected($preset==='year')>{{ __('Tahun ini') }}</option>
                <option value="custom" @selected($preset==='custom')>{{ __('Custom') }}</option>
            </select>
        </div>
        <div>
            <x-input-label :value="__('Dari')" />
            <x-text-input name="start_date" type="date" class="mt-1 text-sm" :value="$start" />
        </div>
        <div>
            <x-input-label :value="__('Sampai')" />
            <x-text-input name="end_date" type="date" class="mt-1 text-sm" :value="$end" />
        </div>
        <div>
            <x-input-label :value="__('Kendaraan')" />
            <select name="vehicle_id" class="mt-1 border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('Semua Kendaraan') }}</option>
                @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected((string)$selectedVehicle===(string)$v->id)>{{ $v->name }}</option>@endforeach
            </select>
        </div>
        <x-primary-button type="submit" class="text-sm h-10">{{ __('Terapkan') }}</x-primary-button>
        <a href="{{ route('reports.fuel', ['preset' => 'month']) }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold h-10 flex items-center">{{ __('Reset') }}</a>
    </form>
    <p class="mt-2 text-xs text-gray-500">{{ __('Periode: :start — :end', ['start' => \Carbon\Carbon::parse($start)->format('d M Y'), 'end' => \Carbon\Carbon::parse($end)->format('d M Y')]) }}</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white shadow-sm sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Biaya') }}</p><p class="mt-2 text-2xl font-semibold text-emerald-600">Rp {{ number_format($stats['total'],0,',','.') }}</p></div>
    <div class="bg-white shadow-sm sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Liter') }}</p><p class="mt-2 text-2xl font-semibold">{{ number_format($stats['liters'],2,',','.') }} L</p></div>
    <div class="bg-white shadow-sm sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Jumlah Pengisian') }}</p><p class="mt-2 text-2xl font-semibold">{{ $stats['count'] }}</p></div>
    <div class="bg-white shadow-sm sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Rata-rata Harga/L') }}</p><p class="mt-2 text-2xl font-semibold">Rp {{ number_format($stats['avg_price'],0,',','.') }}</p><p class="text-xs text-gray-400">{{ __('Rata-rata per isian: :val', ['val' => 'Rp ' . number_format($stats['avg_cost'],0,',','.')]) }}</p></div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">{{ __('Biaya BBM per Bulan') }}</h3>
    <canvas id="fuelMonthly" class="max-h-72"></canvas>
    <div class="overflow-x-auto mt-4">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Bulan') }}</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Biaya') }}</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Liter') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($monthly as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm">{{ $row['label'] }}</td>
                        <td class="px-4 py-2 text-sm text-right">Rp {{ number_format($row['total'],0,',','.') }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format($row['liters'],2,',','.') }} L</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ __('Per Kendaraan') }}</h3>
        @if($perVehicle->isEmpty())
            <p class="text-sm text-gray-400">{{ __('Belum ada data BBM pada periode ini') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kendaraan') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Isi') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Liter') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($perVehicle as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm font-medium">{{ $row['vehicle']->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-sm text-right">{{ $row['count'] }}</td>
                                <td class="px-3 py-2 text-sm text-right">{{ number_format($row['liters'],2,',','.') }}</td>
                                <td class="px-3 py-2 text-sm text-right font-semibold">Rp {{ number_format($row['total'],0,',','.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ __('Riwayat Pengisian') }} <span class="text-sm font-normal text-gray-500">({{ $records->count() }})</span></h3>
        @if($records->isEmpty())
            <p class="text-sm text-gray-400">{{ __('Tidak ada pengisian BBM pada periode ini') }}</p>
        @else
            <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @foreach($records as $r)
                    <li class="flex justify-between py-2 text-sm">
                        <span>{{ $r->fuel_date->format('d M Y') }} • {{ $r->vehicle->name ?? '-' }} • {{ number_format($r->liters,2,',','.') }} L</span>
                        <span class="font-medium">Rp {{ number_format($r->total_cost,0,',','.') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const ctx = document.getElementById('fuelMonthly');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($monthly, 'label')),
                    datasets: [{ label: @json(__('Biaya BBM')), data: @json(array_column($monthly, 'total')), backgroundColor: 'rgba(59,130,246,0.8)' }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }
    })();
</script>
</div>
