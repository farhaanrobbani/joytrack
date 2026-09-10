<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Akun') }}
            </h2>
            <a href="{{ route('accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold text-sm hover:bg-emerald-700 transition-colors">
                {{ __('Tambah Akun') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($accounts->isEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-500">{{ __('Belum ada akun. Buat akun baru untuk mulai mencatat transaksi Anda.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($accounts as $account)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $account->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $account->type_label }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $account->is_active ? __('Aktif') : __('Nonaktif') }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs text-gray-500">{{ __('Saldo') }}</p>
                            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</p>
                        </div>

                        @if ($account->description)
                            <p class="text-sm text-gray-600 mb-4">{{ $account->description }}</p>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('accounts.edit', $account) }}" class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded text-center text-sm font-medium hover:bg-blue-100 transition-colors">
                                {{ __('Edit') }}
                            </a>
                            <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('Yakin hapus akun ini?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 bg-red-50 text-red-600 rounded text-sm font-medium hover:bg-red-100 transition-colors">
                                    {{ __('Hapus') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
