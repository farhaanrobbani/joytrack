<x-guest-layout>
    <div class="text-center py-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
            <x-heroicon-o-arrow-path class="w-8 h-8 text-amber-600" />
        </div>
        <p class="text-sm font-semibold text-emerald-600 mb-1">419</p>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Sesi Berakhir') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Halaman kedaluwarsa karena terlalu lama diam. Muat ulang untuk melanjutkan.') }}</p>
        <button type="button" onclick="location.reload()" class="mt-6 inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700">{{ __('Muat Ulang') }}</button>
    </div>
</x-guest-layout>
