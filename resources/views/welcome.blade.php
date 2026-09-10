<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\SiteSetting::get('site_name', 'JoyTrack');
        $heroTitle = \App\Models\SiteSetting::get('hero_title', 'Kelola Keuangan & Kendaraan dalam Satu Tempat');
        $heroSubtitle = \App\Models\SiteSetting::get('hero_subtitle', 'Catat transaksi, pantau saldo, kelola BBM & servis, dapatkan laporan keuangan & kendaraan — semua dengan JoyTrack yang modern dan bisa di-install di HP.');
        $iconUrl = '/icons/icon-192x192.png?v=2';
    @endphp
    <title>{{ $siteName }} — Manajemen Keuangan & Kendaraan</title>
    <meta name="description" content="{{ $heroSubtitle }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32x32.png?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png?v=2">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png?v=2">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    <!-- Nav -->
    <header class="fixed top-0 left-0 right-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ $iconUrl }}" alt="{{ $siteName }}" class="w-8 h-8 rounded-xl object-cover">
                <span class="font-bold text-gray-900 dark:text-white">{{ $siteName }}</span>
            </a>
            <div class="flex items-center gap-2">
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 shadow-soft">{{ __('Ke Dashboard') }}</a>
                    <a href="{{ route('dashboard') }}" class="sm:hidden p-2 rounded-xl bg-brand-600 text-white"><x-heroicon-o-arrow-right class="w-5 h-5" /></a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">{{ __('Masuk') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 shadow-soft">{{ __('Daftar') }}</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main class="pt-16">
        <!-- Hero -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-xs font-semibold border border-brand-100 dark:border-brand-800">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                        {{ __('PWA • Offline Ready • Installable') }}
                    </span>
                    <h1 class="mt-4 text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $heroTitle }}
                    </h1>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        {{ $heroSubtitle }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700 shadow-soft-lg">{{ __('Ke Dashboard') }} <x-heroicon-o-arrow-right class="w-4 h-4" /></a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700 shadow-soft-lg">{{ __('Masuk') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700">{{ __('Daftar Gratis') }}</a>
                            @endif
                        @endauth
                    </div>
                    <div class="mt-6 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1"><x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500" /> {{ __('98 tests passed') }}</span>
                        <span class="flex items-center gap-1"><x-heroicon-o-device-phone-mobile class="w-4 h-4" /> {{ __('Mobile & Tailscale Ready') }}</span>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-br from-brand-100 to-emerald-50 dark:from-brand-900/20 dark:to-gray-800 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-soft-lg border border-gray-100 dark:border-gray-700 p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-auto text-xs text-gray-400">JoyTrack Dashboard</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 dark:bg-gray-700 p-4"><p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total Saldo') }}</p><p class="font-bold tabular-nums">Rp 12.500.000</p></div>
                            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-4"><p class="text-xs text-gray-500">{{ __('Pemasukan') }}</p><p class="font-bold text-emerald-600">Rp 5.000.000</p></div>
                            <div class="rounded-xl bg-red-50 dark:bg-red-900/30 p-4"><p class="text-xs text-gray-500">{{ __('Pengeluaran') }}</p><p class="font-bold text-red-600">Rp 2.300.000</p></div>
                            <div class="rounded-xl bg-brand-50 dark:bg-brand-900/30 p-4"><p class="text-xs text-gray-500">{{ __('Kendaraan') }}</p><p class="font-bold">Vario 45.200 km</p></div>
                        </div>
                        <div class="mt-4 h-20 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-400 opacity-90"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-soft border border-gray-100 dark:border-gray-800 p-6 hover:shadow-soft-lg transition">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold">{{ __('Keuangan') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Akun bank, cash, e-wallet, kategori, transaksi income/expense/transfer dengan saldo terpusat yang aman.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-soft border border-gray-100 dark:border-gray-800 p-6 hover:shadow-soft-lg transition">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
                        <x-heroicon-o-truck class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold">{{ __('Kendaraan') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Catat BBM (km/L, biaya/km) & servis (jasa+sparepart), otomatis buat transaksi kategori Kendaraan.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-soft border border-gray-100 dark:border-gray-800 p-6 hover:shadow-soft-lg transition">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4">
                        <x-heroicon-o-document-chart-bar class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold">{{ __('Laporan & Export') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Cashflow 6 bulan, kategori, per kendaraan — export Excel & PDF, pengingat servis & PWA offline.') }}</p>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
            <h2 class="text-xl font-bold text-center">{{ __('FAQ') }}</h2>
            <div class="mt-6 space-y-4">
                <details class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                    <summary class="font-medium cursor-pointer">{{ __('Apakah data saya aman?') }}</summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Semua data terisolasi per user via Policies, validasi & transaksi atomik. Password ter-hash, file disimpan via Storage.') }}</p>
                </details>
                <details class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                    <summary class="font-medium cursor-pointer">{{ __('Bisa di-install di HP?') }}</summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Ya, JoyTrack adalah PWA — buka di Chrome Android → Install. Bisa offline untuk lihat dashboard.') }}</p>
                </details>
                <details class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                    <summary class="font-medium cursor-pointer">{{ __('Bagaimana kategori Kendaraan?') }}</summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Pengeluaran BBM & servis otomatis masuk kategori Kendaraan, sehingga laporan keuangan konsolidasi.') }}</p>
                </details>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 dark:border-gray-800 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
            <span>© {{ date('Y') }} {{ $siteName }} — {{ __('Manajemen Keuangan & Kendaraan') }}</span>
            <div class="flex gap-4">
                <a href="{{ route('login') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('Masuk') }}</a>
                @if (Route::has('register'))<a href="{{ route('register') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('Daftar') }}</a>@endif
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('Dashboard') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
