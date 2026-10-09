<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Berlangganan') }}</h2>
            <a href="{{ route('subscriptions.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah Berlangganan') }}</a>
        </div>
    </x-slot>

    <livewire:subscriptions-index />
</x-app-layout>
