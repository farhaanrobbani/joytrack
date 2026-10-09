<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Kelola Beranda') }}</h2>
    </x-slot>


    <livewire:admin-settings-edit :settings="$settings" />
</x-app-layout>
