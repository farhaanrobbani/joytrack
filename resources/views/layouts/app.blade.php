<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JoyTrack') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <!-- Topbar -->
            <header class="fixed top-0 left-0 right-0 z-30 h-16 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-full px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Hamburger (mobile) -->
                        <button
                            @click="sidebarOpen = true"
                            class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="{{ __('Buka menu') }}"
                        >
                            <x-heroicon-o-bars-3 class="w-6 h-6" />
                        </button>

                        <a href="{{ route('dashboard') }}" class="md:hidden font-bold text-gray-800">
                            {{ config('app.name', 'JoyTrack') }}
                        </a>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 p-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <x-heroicon-o-user-circle class="w-7 h-7 text-gray-400" />
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('logout')" method="post" as="button">
                                    {{ __('Keluar') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <!-- Sidebar (desktop) -->
            <aside class="hidden md:block fixed top-16 bottom-0 left-0 w-60 bg-white border-r border-gray-200">
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
                    class="absolute top-0 bottom-0 left-0 w-60 bg-white shadow-xl"
                >
                    <div class="flex items-center justify-between px-4 pt-4">
                        <span class="font-bold text-gray-800">{{ config('app.name', 'JoyTrack') }}</span>
                        <button @click="sidebarOpen = false" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="{{ __('Tutup menu') }}">
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
                            <h1 class="text-xl font-semibold text-gray-800">{{ $header }}</h1>
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
