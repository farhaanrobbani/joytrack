<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Kategori') }}</h2></x-slot>

    <div class="max-w-2xl mx-auto">
        <livewire:categories-create />
    </div>
</x-app-layout>
