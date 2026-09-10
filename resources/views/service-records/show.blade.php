<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detail Servis') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('service-records.edit', $serviceRecord) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Edit') }}</a>
                <a href="{{ route('service-records.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">{{ __('Kendaraan') }}</dt><dd class="font-medium">{{ $serviceRecord->vehicle->name }} ({{ $serviceRecord->vehicle->license_plate }})</dd></div>
                <div><dt class="text-gray-500">{{ __('Tanggal') }}</dt><dd class="font-medium">{{ $serviceRecord->service_date->format('d M Y') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Odometer') }}</dt><dd class="font-medium">{{ number_format($serviceRecord->odometer,0,',','.') }} km</dd></div>
                <div><dt class="text-gray-500">{{ __('Jenis Servis') }}</dt><dd class="font-medium">{{ $serviceRecord->service_type }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Bengkel') }}</dt><dd class="font-medium">{{ $serviceRecord->workshop ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Biaya Jasa') }}</dt><dd class="font-medium">Rp {{ number_format($serviceRecord->labor_cost,0,',','.') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Biaya Sparepart') }}</dt><dd class="font-medium">Rp {{ number_format($serviceRecord->parts_cost,0,',','.') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Total Biaya') }}</dt><dd class="font-bold text-lg">Rp {{ number_format($serviceRecord->total_cost,0,',','.') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Servis Berikutnya (Tanggal)') }}</dt><dd class="font-medium">{{ $serviceRecord->next_service_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Servis Berikutnya (KM)') }}</dt><dd class="font-medium">{{ $serviceRecord->next_service_odometer ? number_format($serviceRecord->next_service_odometer,0,',','.') . ' km' : '-' }}</dd></div>
                @if($serviceRecord->account)<div><dt class="text-gray-500">{{ __('Akun') }}</dt><dd class="font-medium">{{ $serviceRecord->account->name }}</dd></div>@endif
                @if($serviceRecord->transaction)<div><dt class="text-gray-500">{{ __('Transaksi') }}</dt><dd><a href="{{ route('transactions.show', $serviceRecord->transaction) }}" class="text-emerald-600 hover:underline">{{ __('Lihat transaksi') }}</a></dd></div>@endif
            </dl>
            @if($serviceRecord->notes)<p class="mt-4 text-sm text-gray-600 whitespace-pre-wrap border-t pt-4">{{ $serviceRecord->notes }}</p>@endif
        </div>
        <x-attachment-list :model="$serviceRecord" type="service_record" />
    </div>
</x-app-layout>
