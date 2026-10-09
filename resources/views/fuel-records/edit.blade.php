<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit BBM') }}</h2></x-slot>

    <div class="max-w-3xl mx-auto">
        <livewire:fuel-records-edit :fuel-record="$fuelRecord" />
    </div>
</x-app-layout>
