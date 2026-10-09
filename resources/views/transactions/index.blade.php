<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Transaksi') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('+ Pemasukan') }}</a>
                <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">{{ __('+ Pengeluaran') }}</a>
                <a href="{{ route('transactions.create', ['type' => 'transfer']) }}" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800">{{ __('+ Transfer') }}</a>
            </div>
        </div>
    </x-slot>

    <livewire:transactions-index />
</x-app-layout>
