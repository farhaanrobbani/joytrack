<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kendaraan') }}</h2>
            <a href="{{ route('vehicles.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah Kendaraan') }}</a>
        </div>
    </x-slot>

    @if($vehicles->isEmpty())
        <div class="bg-white shadow sm:rounded-lg p-8 text-center">
            <p class="text-gray-500">{{ __('Belum ada kendaraan') }}</p>
            <p class="mt-1 text-sm text-gray-400">{{ __('Tambahkan kendaraan untuk mencatat bahan bakar dan servis.') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vehicles as $v)
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $v->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $v->license_plate ?? '-' }} @if($v->brand) • {{ $v->brand }} @endif</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $v->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">{{ $v->is_active ? __('Aktif') : __('Nonaktif') }}</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-600 mb-4">
                        <p>{{ __('Odometer') }}: <span class="font-medium text-gray-900">{{ number_format($v->current_odometer,0,',','.') }} km</span></p>
                        @if($v->year) <p>{{ __('Tahun') }}: {{ $v->year }} @if($v->color) • {{ $v->color }} @endif</p> @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('vehicles.show', $v) }}" class="flex-1 text-center px-3 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-medium hover:bg-emerald-100">{{ __('Detail') }}</a>
                        <a href="{{ route('vehicles.edit', $v) }}" class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100">{{ __('Edit') }}</a>
                        <form action="{{ route('vehicles.destroy', $v) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('Yakin hapus kendaraan ini?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100">{{ __('Hapus') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
