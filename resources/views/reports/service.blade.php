<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Servis') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('export.service.excel', request()->only(['start_date','end_date','vehicle_id'])) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">{{ __('Export Excel') }}</a>
                <a href="{{ route('export.service.pdf', request()->only(['start_date','end_date','preset','vehicle_id'])) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">{{ __('Export PDF') }}</a>
                <a href="{{ route('export.service', request()->only(['start_date','end_date','vehicle_id'])) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800">{{ __('Export CSV') }}</a>
            </div>
        </div>
    </x-slot>


    <livewire:reports-service :preset="$preset" :startDate="$start" :endDate="$end" :vehicleId="$selectedVehicle" />
</x-app-layout>
