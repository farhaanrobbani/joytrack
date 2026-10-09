<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Transaksi') }}</h2></x-slot>

    <div class="max-w-2xl mx-auto">
        <livewire:transactions-create :type="$type" />
    </div>
</x-app-layout>
