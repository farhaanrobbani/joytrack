<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">{{ __('Dashboard') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Ringkasan keuangan dan kendaraan Anda') }}</p>
            </div>
        </div>
    </x-slot>

    <livewire:dashboard-index />
</x-app-layout>
