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
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $iconUrlGuest }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ $iconUrlGuest }}">
        <link rel="apple-touch-icon" href="{{ $iconUrlGuest }}">
        <link rel="icon" href="/favicon.ico" sizes="any">

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
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased bg-gray-100 dark:bg-gray-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-950">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-900 shadow-md overflow-hidden sm:rounded-lg border border-transparent dark:border-gray-800">
                {{ $slot }}
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    {{ __('Kembali ke Beranda') }}
                </a>
            </div>
        </div>
    </body>
</html>
