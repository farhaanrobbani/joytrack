<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Kelola User') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
        </div>
    </x-slot>


    <livewire:admin-users-index  />
</x-app-layout>
