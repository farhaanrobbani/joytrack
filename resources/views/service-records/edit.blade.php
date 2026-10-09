<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Servis') }}</h2></x-slot>

    <div class="max-w-3xl mx-auto">
        <livewire:service-records-edit :service-record="$serviceRecord" />
    </div>
</x-app-layout>
