<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Admin Dashboard') }}</h2>
    </x-slot>


    <livewire:admin-dashboard :totalUsers="$totalUsers" :activeUsers="$activeUsers" :adminUsers="$adminUsers" :recentUsers="$recentUsers" />
</x-app-layout>
