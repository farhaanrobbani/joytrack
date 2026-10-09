<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kendaraan') }}</h2>
            <a href="{{ route('vehicles.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah Kendaraan') }}</a>
        </div>
    </x-slot>

    <livewire:vehicles-index />
</x-app-layout>
