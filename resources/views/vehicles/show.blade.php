<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $vehicle->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Edit') }}</a>
                <a href="{{ route('vehicles.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
            </div>
        </div>
    </x-slot>

    @if(isset($reminder) && $reminder)
        <div class="rounded-lg border p-4 mb-6 {{ $reminder['status']==='overdue' ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200' }}">
            <p class="font-semibold {{ $reminder['status']==='overdue' ? 'text-red-800' : 'text-amber-800' }}">
                {{ $reminder['status']==='overdue' ? __('Servis Terlambat') : __('Servis Segera Jatuh Tempo') }}
            </p>
            <p class="text-sm {{ $reminder['status']==='overdue' ? 'text-red-700' : 'text-amber-700' }}">
                @if($reminder['record']->next_service_date) {{ __('Tanggal: :date', ['date' => $reminder['record']->next_service_date->format('d M Y')]) }} @endif
                @if($reminder['record']->next_service_date && $reminder['record']->next_service_odometer) • @endif
                @if($reminder['record']->next_service_odometer) {{ __('KM: :km', ['km' => number_format($reminder['record']->next_service_odometer,0,',','.')]) }} @endif
                — {{ $reminder['record']->service_type }} @if($reminder['record']->workshop) ({{ $reminder['record']->workshop }}) @endif
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ $vehicle->name }}</h3>
                <p class="text-sm text-gray-500 mb-1">{{ $vehicle->license_plate ?? '-' }} @if($vehicle->brand) • {{ $vehicle->brand }} {{ $vehicle->model }} @endif</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($vehicle->current_odometer,0,',','.') }} km</p>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">{{ __('Tahun') }}</span><p class="font-medium">{{ $vehicle->year ?? '-' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Warna') }}</span><p class="font-medium">{{ $vehicle->color ?? '-' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Jenis') }}</span><p class="font-medium">{{ $vehicle->vehicle_type ?? '-' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Status') }}</span><p><span class="px-2 py-1 text-xs rounded-full {{ $vehicle->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">{{ $vehicle->is_active ? __('Aktif') : __('Nonaktif') }}</span></p></div>
                    @if($vehicle->purchase_price)<div><span class="text-gray-500">{{ __('Harga Beli') }}</span><p class="font-medium">Rp {{ number_format($vehicle->purchase_price,0,',','.') }}</p></div>@endif
                    @if($vehicle->purchase_date)<div><span class="text-gray-500">{{ __('Tanggal Beli') }}</span><p class="font-medium">{{ $vehicle->purchase_date->format('d M Y') }}</p></div>@endif
                </div>
                @if($vehicle->notes)<p class="mt-4 text-sm text-gray-600 whitespace-pre-wrap">{{ $vehicle->notes }}</p>@endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">{{ __('Ringkasan Biaya') }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('BBM') }}</span><span class="font-medium">Rp {{ number_format($stats['total_fuel_cost'],0,',','.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Servis') }}</span><span class="font-medium">Rp {{ number_format($stats['total_service_cost'],0,',','.') }}</span></div>
                    <div class="flex justify-between border-t pt-2 font-semibold"><span>{{ __('Total') }}</span><span>Rp {{ number_format($stats['total_fuel_cost']+$stats['total_service_cost'],0,',','.') }}</span></div>
                    <p class="text-xs text-gray-400">{{ __(':fuel pengisian, :service servis', ['fuel' => $stats['fuel_count'], 'service' => $stats['service_count']]) }}</p>
                </div>
            </div>

            @if(isset($recentFuels) && $recentFuels->isNotEmpty())
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">{{ __('Riwayat BBM Terbaru') }}</h3>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach($recentFuels as $f)
                            <li class="flex justify-between py-2">
                                <span>{{ $f->fuel_date->format('d M Y') }} • {{ number_format($f->liters,2,',','.') }} L</span>
                                <span class="font-medium">Rp {{ number_format($f->total_cost,0,',','.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('fuel-records.index', ['vehicle_id' => $vehicle->id]) }}" class="mt-3 inline-block text-sm text-emerald-600 hover:underline">{{ __('Lihat semua BBM') }}</a>
                </div>
            @else
                <div class="bg-gray-50 border border-dashed rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">{{ __('Belum ada riwayat BBM untuk kendaraan ini') }}</p>
                    <a href="{{ route('fuel-records.create', ['vehicle_id' => $vehicle->id]) }}" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">{{ __('Tambah BBM') }}</a>
                </div>
            @endif

            @if(isset($recentServices) && $recentServices->isNotEmpty())
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">{{ __('Riwayat Servis Terbaru') }}</h3>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach($recentServices as $s)
                            <li class="flex justify-between py-2">
                                <span>{{ $s->service_date->format('d M Y') }} • {{ $s->service_type }}</span>
                                <span class="font-medium">Rp {{ number_format($s->total_cost,0,',','.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('service-records.index', ['vehicle_id' => $vehicle->id]) }}" class="mt-3 inline-block text-sm text-emerald-600 hover:underline">{{ __('Lihat semua Servis') }}</a>
                </div>
            @else
                <div class="bg-gray-50 border border-dashed rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">{{ __('Belum ada riwayat servis untuk kendaraan ini') }}</p>
                    <a href="{{ route('service-records.create', ['vehicle_id' => $vehicle->id]) }}" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">{{ __('Tambah Servis') }}</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
