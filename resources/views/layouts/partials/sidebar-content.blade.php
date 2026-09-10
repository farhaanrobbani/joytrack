<nav class="flex flex-col flex-1 min-h-0 py-6 overflow-y-auto">
    <div class="px-6 mb-8">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ config('app.name', 'JoyTrack') }}</span>
        </a>
    </div>

    <div class="px-4 space-y-6">
        <x-sidebar-section :items="[
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
        ]" :title="__('Umum')" />

        <x-sidebar-section :items="[
            ['label' => 'Transaksi', 'route' => 'transactions.index', 'icon' => 'banknotes'],
            ['label' => 'Akun', 'route' => 'accounts.index', 'icon' => 'wallet'],
            ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'tag'],
        ]" :title="__('Keuangan')" />

        <x-sidebar-section :items="[
            ['label' => 'Kendaraan', 'route' => 'vehicles.index', 'icon' => 'truck'],
            ['label' => 'Bahan Bakar', 'route' => 'fuel-records.index', 'icon' => 'beaker'],
            ['label' => 'Servis', 'route' => 'service-records.index', 'icon' => 'wrench'],
        ]" :title="__('Kendaraan')" />

        <x-sidebar-section :items="[
            ['label' => 'Laporan Keuangan', 'route' => 'reports.finance', 'icon' => 'chart-bar'],
            ['label' => 'Laporan Kendaraan', 'route' => 'reports.vehicle', 'icon' => 'document-chart-bar'],
        ]" :title="__('Laporan')" />

        @if(auth()->check() && auth()->user()->isAdmin())
            <x-sidebar-section :items="[
                ['label' => 'Admin Dashboard', 'route' => 'admin.dashboard', 'icon' => 'shield-check'],
                ['label' => 'Kelola User', 'route' => 'admin.users.index', 'icon' => 'users'],
                ['label' => 'Kelola Beranda', 'route' => 'admin.settings.edit', 'icon' => 'photo'],
            ]" :title="__('Admin')" />
        @endif
    </div>
</nav>
