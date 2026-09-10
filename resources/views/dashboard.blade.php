<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Total Saldo') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800">Rp 0</p>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Pemasukan Bulan Ini') }}</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">Rp 0</p>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Pengeluaran Bulan Ini') }}</p>
            <p class="mt-2 text-2xl font-semibold text-red-600">Rp 0</p>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Selisih') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800">Rp 0</p>
        </div>
    </div>

    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <p class="text-gray-500">{{ __('Belum ada transaksi. Mulai catat pemasukan atau pengeluaran untuk melihat kondisi keuangan Anda.') }}</p>
        </div>
    </div>
</x-app-layout>
