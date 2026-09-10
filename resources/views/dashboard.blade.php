<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">{{ __('Dashboard') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Ringkasan keuangan dan kendaraan Anda') }}</p>
            </div>
        </div>
    </x-slot>

    @if(isset($reminders) && $reminders->isNotEmpty())
        <div class="mb-6 space-y-3">
            @foreach($reminders as $r)
                <div class="rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold {{ $r['status']==='overdue' ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300' }}">
                                {{ $r['status']==='overdue' ? __('Servis Terlambat') : __('Servis Segera Jatuh Tempo') }} — {{ $r['vehicle']->name }} ({{ $r['vehicle']->license_plate ?? '-' }})
                            </p>
                            <ul class="mt-1 list-disc list-inside text-sm {{ $r['status']==='overdue' ? 'text-red-700 dark:text-red-400' : 'text-amber-700 dark:text-amber-400' }}">
                                @foreach($r['messages'] as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Servis terakhir') }}: {{ $r['record']->service_date->format('d M Y') }} • {{ $r['record']->service_type }} • {{ $r['record']->workshop ?? '-' }}</p>
                        </div>
                        <a href="{{ route('vehicles.show', $r['vehicle']) }}" class="shrink-0 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700">{{ __('Lihat') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card :label="__('Total Saldo')" :value="'Rp ' . number_format($totalBalance, 0, ',', '.')" icon="wallet" color="brand" />
        <x-stat-card :label="__('Pemasukan Bulan Ini')" :value="'Rp ' . number_format($monthlyIncome, 0, ',', '.')" icon="arrow-trending-up" color="emerald" />
        <x-stat-card :label="__('Pengeluaran Bulan Ini')" :value="'Rp ' . number_format($monthlyExpense, 0, ',', '.')" icon="arrow-trending-down" color="red" />
        <x-stat-card :label="__('Selisih')" :value="'Rp ' . number_format($netCashflow, 0, ',', '.')" icon="scale" color="{{ $netCashflow >=0 ? 'emerald' : 'red' }}" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <x-card>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ __('Cashflow 6 Bulan') }}
            </h3>
            @if(array_sum($cashflowChart['income']) === 0.0 && array_sum($cashflowChart['expense']) === 0.0)
                <div class="py-12 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-gray-400" />
                    </div>
                    <p class="text-sm text-gray-400">{{ __('Belum ada data cashflow') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Mulai catat transaksi untuk melihat grafik') }}</p>
                </div>
            @else
                <canvas id="cashflowChart" height="220"></canvas>
            @endif
        </x-card>

        <x-card>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                {{ __('Pengeluaran per Kategori (Bulan Ini)') }}
            </h3>
            @if($expenseByCategory->isEmpty())
                <div class="py-12 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <x-heroicon-o-tag class="w-6 h-6 text-gray-400" />
                    </div>
                    <p class="text-sm text-gray-400">{{ __('Belum ada pengeluaran bulan ini') }}</p>
                </div>
            @else
                <canvas id="categoryChart" height="220"></canvas>
                <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($expenseByCategory as $cat)
                        <li class="flex justify-between py-2.5 text-sm">
                            <span class="text-gray-600 dark:text-gray-300">{{ $cat['name'] }}</span>
                            <span class="font-semibold tabular-nums">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    <x-card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-5 h-5 text-gray-400" />
                {{ __('Transaksi Terbaru') }}
            </h3>
            <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Lihat semua') }} →</a>
        </div>
        @if($recentTransactions->isEmpty())
            <div class="py-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-3">
                    <x-heroicon-o-banknotes class="w-8 h-8 text-gray-300" />
                </div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Belum ada transaksi') }}</p>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">{{ __('Mulai catat pemasukan atau pengeluaran untuk melihat kondisi keuangan Anda.') }}</p>
                <a href="{{ route('transactions.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 shadow-soft">{{ __('+ Tambah Transaksi') }}</a>
            </div>
        @else
            <div class="overflow-x-auto -mx-6">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50/50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Tanggal') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Deskripsi') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Akun') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Nominal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2 {{ $tx->type==='income'?'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300':($tx->type==='expense'?'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300':'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300') }}">{{ $tx->type === 'income' ? __('Pemasukan') : ($tx->type === 'expense' ? __('Pengeluaran') : __('Transfer')) }}</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $tx->description ?? $tx->category->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $tx->account->name }}</td>
                                <td class="px-6 py-4 text-sm font-bold tabular-nums text-right {{ $tx->type==='income'?'text-emerald-600 dark:text-emerald-400':($tx->type==='expense'?'text-red-600 dark:text-red-400':'text-gray-700 dark:text-gray-300') }}">{{ $tx->type==='expense' ? '-' : ($tx->type==='income' ? '+' : '') }} Rp {{ number_format($tx->amount,0,',','.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    @push('scripts')
    @if(array_sum($cashflowChart['income']) !== 0.0 || array_sum($cashflowChart['expense']) !== 0.0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(148,163,184,0.2)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';
            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = gridColor;
            const ctx = document.getElementById('cashflowChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($cashflowChart['labels']),
                        datasets: [
                            { label: @json(__('Pemasukan')), data: @json($cashflowChart['income']), backgroundColor: 'rgba(16,185,129,0.85)', borderRadius: 6 },
                            { label: @json(__('Pengeluaran')), data: @json($cashflowChart['expense']), backgroundColor: 'rgba(239,68,68,0.85)', borderRadius: 6 },
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: gridColor } } } }
                });
            }
            const ctx2 = document.getElementById('categoryChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: @json($expenseByCategory->pluck('name')),
                        datasets: [{ data: @json($expenseByCategory->pluck('total')), backgroundColor: ['#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16'], borderWidth: 0, hoverOffset: 6 }]
                    },
                    options: { responsive: true, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } } }
                });
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>
