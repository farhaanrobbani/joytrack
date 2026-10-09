<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Dokumen') }}</h2></x-slot>

    <div class="max-w-2xl mx-auto">
        <livewire:documents-edit :document="$document" />
    </div>
</x-app-layout>
