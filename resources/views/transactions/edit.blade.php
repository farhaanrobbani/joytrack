<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Transaksi') }}</h2></x-slot>

    <div class="max-w-2xl mx-auto">
        <livewire:transactions-edit :transaction="$transaction" />
    </div>
</x-app-layout>
