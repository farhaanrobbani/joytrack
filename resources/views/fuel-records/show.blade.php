<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detail BBM') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('fuel-records.edit', $fuelRecord) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Edit') }}</a>
                <a href="{{ route('fuel-records.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">{{ __('Kendaraan') }}</dt><dd class="font-medium">{{ $fuelRecord->vehicle->name }} ({{ $fuelRecord->vehicle->license_plate }})</dd></div>
                <div><dt class="text-gray-500">{{ __('Tanggal') }}</dt><dd class="font-medium">{{ $fuelRecord->fuel_date->format('d M Y') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Odometer') }}</dt><dd class="font-medium">{{ number_format($fuelRecord->odometer,0,',','.') }} km</dd></div>
                <div><dt class="text-gray-500">{{ __('Jarak sejak isi sebelumnya') }}</dt><dd class="font-medium">{{ $distance !== null ? number_format($distance,0,',','.') . ' km' : '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Jenis BBM') }}</dt><dd class="font-medium">{{ $fuelRecord->fuel_type ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('SPBU') }}</dt><dd class="font-medium">{{ $fuelRecord->station ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Liter') }}</dt><dd class="font-medium">{{ number_format($fuelRecord->liters,2,',','.') }} L</dd></div>
                <div><dt class="text-gray-500">{{ __('Harga/Liter') }}</dt><dd class="font-medium">Rp {{ number_format($fuelRecord->price_per_liter,0,',','.') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Total Biaya') }}</dt><dd class="font-bold text-lg">Rp {{ number_format($fuelRecord->total_cost,0,',','.') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Konsumsi') }}</dt><dd class="font-medium">{{ $efficiency ? number_format($efficiency,2,',','.') . ' km/L' : '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Biaya/km') }}</dt><dd class="font-medium">{{ $costPerKm ? 'Rp '.number_format($costPerKm,0,',','.') : '-' }}</dd></div>
                @if($fuelRecord->account)<div><dt class="text-gray-500">{{ __('Akun') }}</dt><dd class="font-medium">{{ $fuelRecord->account->name }}</dd></div>@endif
                @if($fuelRecord->transaction)<div><dt class="text-gray-500">{{ __('Transaksi') }}</dt><dd><a href="{{ route('transactions.show', $fuelRecord->transaction) }}" class="text-emerald-600 hover:underline">{{ __('Lihat transaksi') }}</a></dd></div>@endif
            </dl>
            @if($fuelRecord->notes)<p class="mt-4 text-sm text-gray-600 whitespace-pre-wrap border-t pt-4">{{ $fuelRecord->notes }}</p>@endif
        </div>
    </div>
</x-app-layout>
