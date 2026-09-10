<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detail Transaksi') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('transactions.edit', $transaction) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Edit') }}</a>
                <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl bg-white shadow sm:rounded-lg p-6">
        <dl class="space-y-4">
            <div class="flex justify-between"><dt class="text-sm text-gray-500">{{ __('Jenis') }}</dt><dd class="text-sm font-medium">
                <span class="px-2 py-1 rounded-full text-xs {{ $transaction->type==='income'?'bg-emerald-100 text-emerald-700':($transaction->type==='expense'?'bg-red-100 text-red-700':'bg-gray-100 text-gray-700') }}">{{ $transaction->type==='income'?__('Pemasukan'):($transaction->type==='expense'?__('Pengeluaran'):__('Transfer')) }}</span>
            </dd></div>
            <div class="flex justify-between"><dt class="text-sm text-gray-500">{{ __('Tanggal') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $transaction->transaction_date->format('d F Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-sm text-gray-500">{{ __('Nominal') }}</dt><dd class="text-lg font-bold {{ $transaction->type==='income'?'text-emerald-600':($transaction->type==='expense'?'text-red-600':'text-gray-900') }}">{{ $transaction->isExpense() ? '-' : ($transaction->isIncome() ? '+' : '') }} Rp {{ number_format($transaction->amount,0,',','.') }}</dd></div>
            <div class="flex justify-between"><dt class="text-sm text-gray-500">{{ __('Akun') }}</dt><dd class="text-sm text-gray-900">{{ $transaction->account->name }} @if($transaction->isTransfer() && $transaction->destinationAccount) <span class="text-gray-400">→</span> {{ $transaction->destinationAccount->name }} @endif</dd></div>
            @if($transaction->category)
                <div class="flex justify-between"><dt class="text-sm text-gray-500">{{ __('Kategori') }}</dt><dd class="text-sm text-gray-900">{{ $transaction->category->name }}</dd></div>
            @endif
            @if($transaction->description)
                <div><dt class="text-sm text-gray-500">{{ __('Deskripsi') }}</dt><dd class="text-sm text-gray-900">{{ $transaction->description }}</dd></div>
            @endif
            @if($transaction->notes)
                <div><dt class="text-sm text-gray-500">{{ __('Catatan') }}</dt><dd class="text-sm text-gray-900 whitespace-pre-wrap">{{ $transaction->notes }}</dd></div>
            @endif
        </dl>
        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="mt-6" onsubmit="return confirm('{{ __('Yakin hapus transaksi ini?') }}')">
            @csrf @method('DELETE')
            <x-danger-button type="submit">{{ __('Hapus Transaksi') }}</x-danger-button>
        </form>
    </div>

    <div class="max-w-2xl">
        <x-attachment-list :model="$transaction" type="transaction" />
    </div>
</x-app-layout>
