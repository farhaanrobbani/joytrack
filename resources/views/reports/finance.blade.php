<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Keuangan') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('export.finance.excel', request()->only(['start_date','end_date','preset'])) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">{{ __('Export Excel') }}</a>
                <a href="{{ route('export.finance.pdf', request()->only(['start_date','end_date','preset'])) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">{{ __('Export PDF') }}</a>
                <a href="{{ route('export.transactions.excel', request()->only(['start_date','end_date'])) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800">{{ __('Export Transaksi') }}</a>
                <a href="{{ route('export.transactions.pdf', request()->only(['start_date','end_date'])) }}" class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-sm font-medium hover:bg-rose-700">{{ __('PDF Transaksi') }}</a>
            </div>
        </div>
    </x-slot>


    <livewire:reports-finance :preset="$preset" :startDate="$start" :endDate="$end" />
</x-app-layout>
