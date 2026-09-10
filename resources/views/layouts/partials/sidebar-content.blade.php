<nav class="flex flex-col h-full py-6">
    <div class="px-6 mb-8">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="text-xl font-bold text-gray-800">{{ config('app.name', 'JoyTrack') }}</span>
        </a>
    </div>

    <div class="px-4 space-y-6">
        <x-sidebar-section :items="[
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
        ]" :title="__('Umum')" />

        <x-sidebar-section :items="[
            ['label' => 'Transaksi', 'route' => 'transactions.index', 'icon' => 'arrows'],
            ['label' => 'Akun', 'route' => 'accounts.index', 'icon' => 'wallet'],
            ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'tag'],
        ]" :title="__('Keuangan')" />

        <x-sidebar-section :items="[
            ['label' => 'Kendaraan', 'route' => 'vehicles.index', 'icon' => 'car'],
            ['label' => 'Bahan Bakar', 'route' => 'fuel-records.index', 'icon' => 'fuel'],
            ['label' => 'Servis', 'route' => 'service-records.index', 'icon' => 'wrench'],
        ]" :title="__('Kendaraan')" />

        <x-sidebar-section :items="[
            ['label' => 'Laporan Keuangan', 'route' => 'reports.finance', 'icon' => 'chart'],
            ['label' => 'Laporan Kendaraan', 'route' => 'reports.vehicle', 'icon' => 'report'],
        ]" :title="__('Laporan')" />
    </div>
</nav>
