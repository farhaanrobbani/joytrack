<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">{{ __('Pengingat') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Servis, dokumen kadaluarsa, dan perpanjangan berlangganan') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('documents.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('+ Tambah Dokumen') }}</a>
                <a href="{{ route('subscriptions.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('+ Tambah Berlangganan') }}</a>
            </div>
        </div>
    </x-slot>

    <livewire:reminders-index />
</x-app-layout>
