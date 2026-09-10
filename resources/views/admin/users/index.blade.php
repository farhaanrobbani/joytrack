<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Kelola User') }} <span class="text-sm font-normal text-gray-500">({{ __('Jumlah user aktif: :count', ['count' => $users->total()]) }})</span></h2>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
        </div>
    </x-slot>

    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama/email') }}" class="flex-1 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl text-sm">
            <x-primary-button type="submit">{{ __('Cari') }}</x-primary-button>
        </form>
    </x-card>

    <x-card>
        <div class="overflow-x-auto -mx-6">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($users as $u)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $u->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $u->email }}</td>
                            <td class="px-6 py-4 text-sm"><span class="px-2 py-1 rounded-full text-xs {{ $u->role==='admin' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">{{ $u->role }}</span></td>
                            <td class="px-6 py-4 text-sm text-right">
                                <form action="{{ route('admin.users.toggle', $u) }}" method="POST" onsubmit="return confirm('{{ __('Ubah role user ini?') }}')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $u->role==='admin' ? 'bg-gray-100 hover:bg-gray-200' : 'bg-amber-100 hover:bg-amber-200 text-amber-800' }}">{{ $u->role==='admin' ? __('Jadikan User') : __('Jadikan Admin') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </x-card>
</x-app-layout>
