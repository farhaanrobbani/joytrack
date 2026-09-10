<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $siteNameGuest = \App\Models\SiteSetting::get('site_name', 'JoyTrack');
            $siteIconGuest = \App\Models\SiteSetting::get('site_icon');
            $iconUrlGuest = $siteIconGuest ? \Illuminate\Support\Facades\Storage::disk('public')->url($siteIconGuest) : '/icons/icon-192x192.png';
        @endphp
        <title>{{ $siteNameGuest }} — {{ __('Masuk') }}</title>
        <meta name="theme-color" content="#059669">
        <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32x32.png?v=2">
        <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png?v=2">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png?v=2">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            (function() {
                try {
                    const stored = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (stored === 'dark' || (!stored && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-brand-50 via-slate-50 to-emerald-50 dark:from-gray-950 dark:via-gray-900 dark:to-brand-950 relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-100 dark:bg-brand-900/20 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-emerald-100 dark:bg-emerald-900/20 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center w-full px-4">
                <a href="/" class="flex flex-col items-center gap-2">
                    <x-application-logo class="w-20 h-20 rounded-2xl shadow-soft" />
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $siteNameGuest }}</span>
                </a>

                <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white/80 dark:bg-gray-900/80 backdrop-blur rounded-2xl shadow-soft-lg border border-white/60 dark:border-gray-800">
                    {{ $slot }}
                </div>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    {{ __('Kembali ke Beranda') }}
                </a>
            </div>
            </div>
        </div>
    </body>
</html>
