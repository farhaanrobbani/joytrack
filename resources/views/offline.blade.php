<x-guest-layout>
    <div class="text-center py-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
            <x-heroicon-o-wifi class="w-8 h-8 text-amber-600" />
        </div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Anda Offline') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Periksa koneksi internet Anda. Beberapa fitur mungkin tidak tersedia saat offline.') }}</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700">{{ __('Kembali ke Dashboard') }}</a>
    </div>
</x-guest-layout>
