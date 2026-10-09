<?php

use App\Models\Category;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view([
            'categories' => Category::where('user_id', auth()->id())
                ->orderBy('type')
                ->orderBy('name')
                ->get()
                ->groupBy('type'),
        ]);
    }
};
?>

<div>
    @if(($categories['income'] ?? collect())->isEmpty() && ($categories['expense'] ?? collect())->isEmpty())
        <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg p-6">
            <p class="text-gray-500">{{ __('Belum ada kategori. Buat kategori untuk mengelompokkan transaksi.') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach (['income' => __('Pemasukan'), 'expense' => __('Pengeluaran')] as $type => $label)
                <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg p-6">
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
                                <li wire:key="cat-{{ $cat->id }}" class="flex items-center justify-between py-3">
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
</div>
