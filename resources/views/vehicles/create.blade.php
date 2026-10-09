<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Kendaraan') }}</h2></x-slot>

    <div class="max-w-3xl mx-auto">
        <livewire:vehicles-create />
    </div>
</x-app-layout>
