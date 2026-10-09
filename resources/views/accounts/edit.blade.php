<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Akun') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <livewire:accounts-edit :account="$account" />
    </div>
</x-app-layout>
