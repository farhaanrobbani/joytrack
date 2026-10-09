<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Servis') }}</h2></x-slot>

    <div class="max-w-3xl mx-auto">
        <livewire:service-records-create :selected-vehicle="$selectedVehicle" />
    </div>
</x-app-layout>
