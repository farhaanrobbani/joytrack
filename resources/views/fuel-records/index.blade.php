<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bahan Bakar') }}</h2>
            <a href="{{ route('fuel-records.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah BBM') }}</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow sm:rounded-lg p-4"><p class="text-xs text-gray-500">{{ __('Total Liter') }}</p><p class="text-xl font-bold">{{ number_format($stats['totalLiters'],2,',','.') }} L</p></div>
        <div class="bg-white shadow sm:rounded-lg p-4"><p class="text-xs text-gray-500">{{ __('Total Biaya') }}</p><p class="text-xl font-bold">Rp {{ number_format($stats['totalCost'],0,',','.') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-4"><p class="text-xs text-gray-500">{{ __('Rata-rata Harga/L') }}</p><p class="text-xl font-bold">Rp {{ number_format($stats['avgPrice'],0,',','.') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-4"><p class="text-xs text-gray-500">{{ __('Konsumsi') }}</p><p class="text-xl font-bold">{{ $stats['efficiency'] ? number_format($stats['efficiency'],2,',','.') . ' km/L' : '-' }}</p><p class="text-xs text-gray-400">{{ $stats['costPerKm'] ? 'Rp '.number_format($stats['costPerKm'],0,',','.').'/km' : '' }}</p></div>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('fuel-records.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <select name="vehicle_id" class="border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('Semua Kendaraan') }}</option>
                @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->name }}</option>@endforeach
            </select>
            <input type="text" name="fuel_type" value="{{ request('fuel_type') }}" placeholder="{{ __('Jenis BBM') }}" class="border-gray-300 rounded-lg text-sm">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-gray-300 rounded-lg text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-gray-300 rounded-lg text-sm">
            <div class="flex gap-2">
                <x-primary-button type="submit" class="text-sm">{{ __('Filter') }}</x-primary-button>
                <a href="{{ route('fuel-records.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        @if($records->isEmpty())
            <div class="p-8 text-center text-gray-500">{{ __('Belum ada catatan BBM') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kendaraan') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Odometer') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Liter') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Biaya') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($records as $r)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $r->fuel_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->vehicle->name }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format($r->odometer,0,',','.') }} km</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format($r->liters,2,',','.') }} L</td>
                                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($r->total_cost,0,',','.') }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('fuel-records.show', $r) }}" class="text-emerald-600 hover:text-emerald-700 mr-2">{{ __('Lihat') }}</a>
                                    <a href="{{ route('fuel-records.edit', $r) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('fuel-records.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $records->links() }}</div>
        @endif
    </div>
</x-app-layout>
