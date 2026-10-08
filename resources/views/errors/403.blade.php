<x-guest-layout>
    <div class="text-center py-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
            <x-heroicon-o-lock-closed class="w-8 h-8 text-red-600" />
        </div>
        <p class="text-sm font-semibold text-emerald-600 mb-1">403</p>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Akses Ditolak') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Anda tidak memiliki izin untuk mengakses halaman ini.') }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700">{{ __('Kembali ke Beranda') }}</a>
    </div>
</x-guest-layout>
