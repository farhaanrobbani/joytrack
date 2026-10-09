<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $type = '';

    #[Url(as: 'account_id')]
    public string $accountId = '';

    #[Url(as: 'category_id')]
    public string $categoryId = '';

    #[Url(as: 'date_from')]
    public string $dateFrom = '';

    #[Url(as: 'date_to')]
    public string $dateTo = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'type', 'accountId', 'categoryId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'type', 'accountId', 'categoryId', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if (in_array($this->type, ['income', 'expense', 'transfer'], true)) {
            $query->where('type', $this->type);
        }
        if ($this->accountId !== '') {
            $query->where(function ($q) {
                $q->where('account_id', $this->accountId)
                    ->orWhere('destination_account_id', $this->accountId);
            });
        }
        if ($this->categoryId !== '') {
            $query->where('category_id', $this->categoryId);
        }
        if ($this->dateFrom !== '') {
            $query->where('transaction_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->where('transaction_date', '<=', $this->dateTo);
        }
        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        return $this->view([
            'transactions' => $query->paginate(15),
            'accounts' => Account::where('user_id', auth()->id())->orderBy('name')->get(),
            'categories' => Category::where('user_id', auth()->id())->orderBy('name')->get(),
        ]);
    }
};
?>

<div>
    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Cari deskripsi/catatan') }}" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select wire:model.live="type" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Jenis') }}</option>
                <option value="income">{{ __('Pemasukan') }}</option>
                <option value="expense">{{ __('Pengeluaran') }}</option>
                <option value="transfer">{{ __('Transfer') }}</option>
            </select>
            <select wire:model.live="accountId" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Akun') }}</option>
                @foreach($accounts as $acc)
                    <option wire:key="acc-{{ $acc->id }}" value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryId" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Kategori') }}</option>
                @foreach($categories as $cat)
                    <option wire:key="cat-{{ $cat->id }}" value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type_label }})</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <input type="date" wire:model.live="dateTo" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <div class="sm:col-span-2 lg:col-span-6">
                <button type="button" wire:click="resetFilters" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Reset') }}</button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden" wire:loading.class="opacity-50">
        @if($transactions->isEmpty())
            <div class="p-8 text-center">
                <p class="text-gray-500">{{ __('Belum ada transaksi') }}</p>
                <p class="mt-1 text-sm text-gray-400">{{ __('Mulai catat pemasukan atau pengeluaran untuk melihat kondisi keuangan Anda.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jenis') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Akun') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kategori') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Nominal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Deskripsi') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $tx)
                            <tr wire:key="{{ $tx->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($tx->type==='income') bg-emerald-100 text-emerald-700
                                        @elseif($tx->type==='expense') bg-red-100 text-red-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ $tx->type === 'income' ? __('Pemasukan') : ($tx->type === 'expense' ? __('Pengeluaran') : __('Transfer')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $tx->account->name ?? '-' }}
                                    @if($tx->isTransfer() && $tx->destinationAccount)
                                        <span class="text-gray-400">→</span> {{ $tx->destinationAccount->name }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $tx->category->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right whitespace-nowrap
                                    @if($tx->type==='income') text-emerald-600
                                    @elseif($tx->type==='expense') text-red-600
                                    @else text-gray-700 @endif">
                                    @if($tx->type==='expense') - @elseif($tx->type==='income') + @endif Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-[180px]">{{ $tx->description ?? '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('transactions.show', $tx) }}" class="text-emerald-600 hover:text-emerald-700 mr-2">{{ __('Lihat') }}</a>
                                    <a href="{{ route('transactions.edit', $tx) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('transactions.destroy', $tx) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus transaksi ini? Saldo akan disesuaikan.') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
