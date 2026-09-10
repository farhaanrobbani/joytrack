<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Kendaraan') }}</h2></x-slot>

    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('reports.vehicle') }}" class="flex flex-wrap gap-3 items-end">
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
            <a href="{{ route('reports.vehicle', ['preset' => 'month']) }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold h-10 flex items-center">{{ __('Reset') }}</a>
        </form>
        <p class="mt-2 text-xs text-gray-500">{{ __('Periode: :start — :end', ['start' => \Carbon\Carbon::parse($start)->format('d M Y'), 'end' => \Carbon\Carbon::parse($end)->format('d M Y')]) }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Biaya BBM') }}</p><p class="mt-2 text-2xl font-semibold">Rp {{ number_format($fuelStats['total'],0,',','.') }}</p><p class="text-xs text-gray-400">{{ number_format($fuelStats['liters'],2,',','.') }} L • {{ $fuelStats['count'] }} {{ __('pengisian') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Biaya Servis') }}</p><p class="mt-2 text-2xl font-semibold">Rp {{ number_format($serviceStats['total'],0,',','.') }}</p><p class="text-xs text-gray-400">{{ $serviceStats['count'] }} {{ __('servis') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Biaya Kendaraan') }}</p><p class="mt-2 text-2xl font-bold text-emerald-600">Rp {{ number_format($totalVehicleCost,0,',','.') }}</p><p class="text-xs text-gray-400">@if($distance){{ number_format($distance,0,',','.') }} km • {{ $efficiency ? number_format($efficiency,2,',','.') . ' km/L' : '' }} @else - @endif</p></div>
    </div>

    @if($distance)
        <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-2">{{ __('Statistik Perjalanan') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-gray-500">{{ __('Jarak Tempuh') }}</p><p class="font-semibold">{{ number_format($distance,0,',','.') }} km</p></div>
                <div><p class="text-gray-500">{{ __('Konsumsi') }}</p><p class="font-semibold">{{ $efficiency ? number_format($efficiency,2,',','.') . ' km/L' : '-' }}</p></div>
                <div><p class="text-gray-500">{{ __('Biaya/km') }}</p><p class="font-semibold">{{ $costPerKm ? 'Rp '.number_format($costPerKm,0,',','.') : '-' }}</p></div>
                <div><p class="text-gray-500">{{ __('Rata-rata Harga/L') }}</p><p class="font-semibold">Rp {{ number_format($fuelStats['avg_price'],0,',','.') }}</p></div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ __('Biaya per Kendaraan') }}</h3>
        @if($perVehicle->isEmpty())
            <p class="text-sm text-gray-400">{{ __('Belum ada kendaraan') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kendaraan') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('BBM') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Servis') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Total') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('Rincian') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($perVehicle as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">{{ $row['vehicle']->name }} <span class="text-xs text-gray-400">{{ $row['vehicle']->license_plate }}</span></td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($row['fuel_total'],0,',','.') }} <span class="text-xs text-gray-400">({{ $row['fuel_count'] }}x)</span></td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($row['service_total'],0,',','.') }} <span class="text-xs text-gray-400">({{ $row['service_count'] }}x)</span></td>
                                <td class="px-4 py-3 text-sm text-right font-semibold">Rp {{ number_format($row['total'],0,',','.') }}</td>
                                <td class="px-4 py-3 text-center text-sm"><a href="{{ route('vehicles.show', $row['vehicle']) }}" class="text-emerald-600 hover:underline">{{ __('Detail') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
