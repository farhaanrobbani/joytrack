<x-guest-layout>
    <div class="text-center py-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
            <x-heroicon-o-magnifying-glass class="w-8 h-8 text-gray-500" />
        </div>
        <p class="text-sm font-semibold text-emerald-600 mb-1">404</p>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Halaman Tidak Ditemukan') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Halaman yang Anda cari tidak ada atau sudah dipindahkan.') }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700">{{ __('Kembali ke Beranda') }}</a>
    </div>
</x-guest-layout>
