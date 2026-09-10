<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Admin Dashboard') }}</h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card :label="__('Total User')" :value="$totalUsers" icon="users" color="brand" />
        <x-stat-card :label="__('User Aktif')" :value="$activeUsers" icon="user-circle" color="emerald" />
        <x-stat-card :label="__('Admin')" :value="$adminUsers" icon="shield-check" color="amber" />
    </div>

    <x-card>
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('User Terbaru') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Email') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Role') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Dibuat') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($recentUsers as $u)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded-full text-xs {{ $u->role==='admin' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">{{ $u->role }}</span></td>
                            <td class="px-4 py-3 text-sm">{{ $u->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700">{{ __('Kelola User') }}</a>
            <a href="{{ route('admin.settings.edit') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200">{{ __('Kelola Beranda') }}</a>
        </div>
    </x-card>
</x-app-layout>
