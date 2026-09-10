<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $siteNameApp = \App\Models\SiteSetting::get('site_name', 'JoyTrack');
        @endphp
        <title>{{ $siteNameApp }}</title>
        <meta name="theme-color" content="#059669">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
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
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-gray-950">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <!-- Topbar -->
            <header class="fixed top-0 left-0 right-0 z-30 h-16 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between h-full px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Hamburger (mobile) -->
                        <button
                            @click="sidebarOpen = true"
                            class="md:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-200"
                            aria-label="{{ __('Buka menu') }}"
                        >
                            <x-heroicon-o-bars-3 class="w-6 h-6" />
                        </button>

                        <a href="{{ route('dashboard') }}" class="md:hidden font-bold text-gray-800 dark:text-gray-100">
                            {{ config('app.name', 'JoyTrack') }}
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-theme-toggle />
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 p-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <x-heroicon-o-user-circle class="w-7 h-7 text-gray-400" />
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-start px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                                        {{ __('Keluar') }}
                                    </button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <!-- Sidebar (desktop) -->
            <aside class="hidden md:flex flex-col fixed top-16 bottom-0 left-0 w-60 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 overflow-y-auto">
                @include('layouts.partials.sidebar-content')
            </aside>

            <!-- Sidebar (mobile drawer) -->
            <div
                x-show="sidebarOpen"
                x-cloak
                class="fixed inset-0 z-40 md:hidden"
                aria-hidden="true"
            >
                <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-gray-900/50" @click="sidebarOpen = false"></div>

                <aside
                    x-show="sidebarOpen"
                    x-transition:enter="transition-transform duration-200 ease-out"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition-transform duration-200 ease-in"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="absolute top-0 bottom-0 left-0 w-60 bg-white dark:bg-gray-900 shadow-xl flex flex-col overflow-y-auto"
                >
                    <div class="flex items-center justify-between px-4 pt-4 shrink-0">
                        <span class="font-bold text-gray-800 dark:text-gray-100">{{ config('app.name', 'JoyTrack') }}</span>
                        <button @click="sidebarOpen = false" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup menu') }}">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                    @include('layouts.partials.sidebar-content')
                </aside>
            </div>

            <!-- Page Content -->
            <main class="pt-16 md:pl-60">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @if (session('status'))
                        <x-flash-message :status="session('status')" class="mb-4" />
                    @endif

                    @isset($header)
                        <header class="mb-6">
                            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ $header }}</h1>
                        </header>
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
