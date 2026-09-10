<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kategori') }}</h2>
            <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold text-sm hover:bg-emerald-700 transition-colors">{{ __('Tambah Kategori') }}</a>
        </div>
    </x-slot>

    @if(($categories['income'] ?? collect())->isEmpty() && ($categories['expense'] ?? collect())->isEmpty())
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-gray-500">{{ __('Belum ada kategori. Buat kategori untuk mengelompokkan transaksi.') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach (['income' => __('Pemasukan'), 'expense' => __('Pengeluaran')] as $type => $label)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $type === 'income' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $label }}
                        <span class="text-sm font-normal text-gray-500">({{ ($categories[$type] ?? collect())->count() }})</span>
                    </h3>
                    @if(($categories[$type] ?? collect())->isEmpty())
                        <p class="text-sm text-gray-400">{{ __('Belum ada kategori :type', ['type' => strtolower($label)]) }}</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach(($categories[$type] ?? []) as $cat)
                                <li class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-gray-800">{{ $cat->name }}</span>
                                        @unless($cat->is_active)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ __('Nonaktif') }}</span>
                                        @endunless
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('categories.edit', $cat) }}" class="text-sm text-emerald-600 hover:text-emerald-700">{{ __('Edit') }}</a>
                                        <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('{{ __('Yakin hapus kategori ini?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-700">{{ __('Hapus') }}</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
