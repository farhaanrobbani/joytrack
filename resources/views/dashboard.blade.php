<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Total Saldo') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Pemasukan Bulan Ini') }}</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Pengeluaran Bulan Ini') }}</p>
            <p class="mt-2 text-2xl font-semibold text-red-600">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">{{ __('Selisih') }}</p>
            <p class="mt-2 text-2xl font-semibold {{ $netCashflow >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($netCashflow, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Cashflow 6 Bulan') }}</h3>
            @if(array_sum($cashflowChart['income']) === 0.0 && array_sum($cashflowChart['expense']) === 0.0)
                <p class="text-sm text-gray-400 py-8 text-center">{{ __('Belum ada data cashflow') }}</p>
            @else
                <canvas id="cashflowChart" height="220"></canvas>
            @endif
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Pengeluaran per Kategori (Bulan Ini)') }}</h3>
            @if($expenseByCategory->isEmpty())
                <p class="text-sm text-gray-400 py-8 text-center">{{ __('Belum ada pengeluaran bulan ini') }}</p>
            @else
                <canvas id="categoryChart" height="220"></canvas>
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($expenseByCategory as $cat)
                        <li class="flex justify-between py-2 text-sm">
                            <span class="text-gray-600">{{ $cat['name'] }}</span>
                            <span class="font-medium">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">{{ __('Transaksi Terbaru') }}</h3>
            <a href="{{ route('transactions.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">{{ __('Lihat semua') }}</a>
        </div>
        @if($recentTransactions->isEmpty())
            <p class="text-sm text-gray-500 py-4">{{ __('Belum ada transaksi. Mulai catat pemasukan atau pengeluaran untuk melihat kondisi keuangan Anda.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Deskripsi') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Akun') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Nominal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentTransactions as $tx)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full mr-2 {{ $tx->type==='income'?'bg-emerald-100 text-emerald-700':($tx->type==='expense'?'bg-red-100 text-red-700':'bg-gray-100 text-gray-700') }}">{{ $tx->type === 'income' ? __('Pemasukan') : ($tx->type === 'expense' ? __('Pengeluaran') : __('Transfer')) }}</span>
                                    {{ $tx->description ?? $tx->category->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $tx->account->name }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right {{ $tx->type==='income'?'text-emerald-600':($tx->type==='expense'?'text-red-600':'text-gray-700') }}">{{ $tx->type==='expense' ? '-' : ($tx->type==='income' ? '+' : '') }} Rp {{ number_format($tx->amount,0,',','.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @push('scripts')
    @if(array_sum($cashflowChart['income']) !== 0.0 || array_sum($cashflowChart['expense']) !== 0.0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('cashflowChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($cashflowChart['labels']),
                        datasets: [
                            { label: @json(__('Pemasukan')), data: @json($cashflowChart['income']), backgroundColor: 'rgba(16,185,129,0.8)' },
                            { label: @json(__('Pengeluaran')), data: @json($cashflowChart['expense']), backgroundColor: 'rgba(239,68,68,0.8)' },
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
            const ctx2 = document.getElementById('categoryChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: @json($expenseByCategory->pluck('name')),
                        datasets: [{ data: @json($expenseByCategory->pluck('total')), backgroundColor: ['#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16'] }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>
