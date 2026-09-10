<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Transaksi') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('+ Pemasukan') }}</a>
                <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">{{ __('+ Pengeluaran') }}</a>
                <a href="{{ route('transactions.create', ['type' => 'transfer']) }}" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800">{{ __('+ Transfer') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari deskripsi/catatan') }}" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select name="type" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Jenis') }}</option>
                <option value="income" @selected(request('type')==='income')>{{ __('Pemasukan') }}</option>
                <option value="expense" @selected(request('type')==='expense')>{{ __('Pengeluaran') }}</option>
                <option value="transfer" @selected(request('type')==='transfer')>{{ __('Transfer') }}</option>
            </select>
            <select name="account_id" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Akun') }}</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected((string)request('account_id')===(string)$acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <select name="category_id" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">{{ __('Semua Kategori') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->id)>{{ $cat->name }} ({{ $cat->type_label }})</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <div class="sm:col-span-2 lg:col-span-6 flex gap-2">
                <x-primary-button type="submit" class="text-sm">{{ __('Filter') }}</x-primary-button>
                <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
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
                            <tr class="hover:bg-gray-50">
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
</x-app-layout>
