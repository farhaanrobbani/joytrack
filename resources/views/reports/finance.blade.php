<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Keuangan') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('export.finance', request()->only(['start_date','end_date','preset'])) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">{{ __('Export Excel') }}</a>
                <a href="{{ route('export.finance.pdf', request()->only(['start_date','end_date','preset'])) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">{{ __('Export PDF') }}</a>
                <a href="{{ route('export.transactions', request()->only(['start_date','end_date'])) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800">{{ __('Export Transaksi') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow sm:rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('reports.finance') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <x-input-label :value="__('Periode')" />
                <select name="preset" onchange="this.form.submit()" class="mt-1 border-gray-300 rounded-lg text-sm">
                    <option value="today" @selected($preset==='today')>{{ __('Hari ini') }}</option>
                    <option value="week" @selected($preset==='week')>{{ __('Minggu ini') }}</option>
                    <option value="month" @selected($preset==='month')>{{ __('Bulan ini') }}</option>
                    <option value="year" @selected($preset==='year')>{{ __('Tahun ini') }}</option>
                    <option value="custom" @selected($preset==='custom')>{{ __('Custom') }}</option>
                </select>
            </div>
            <div>
                <x-input-label for="start_date" :value="__('Dari')" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 text-sm" :value="$start" />
            </div>
            <div>
                <x-input-label for="end_date" :value="__('Sampai')" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 text-sm" :value="$end" />
            </div>
            <input type="hidden" name="preset" value="custom" id="preset-custom" disabled>
            <x-primary-button type="submit" class="text-sm h-10">{{ __('Terapkan') }}</x-primary-button>
            <a href="{{ route('reports.finance', ['preset' => 'month']) }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold h-10 flex items-center">{{ __('Reset') }}</a>
        </form>
        <p class="mt-2 text-xs text-gray-500">{{ __('Periode: :start — :end', ['start' => \Carbon\Carbon::parse($start)->format('d M Y'), 'end' => \Carbon\Carbon::parse($end)->format('d M Y')]) }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Pemasukan') }}</p><p class="mt-2 text-2xl font-semibold text-emerald-600">Rp {{ number_format($totalIncome,0,',','.') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Pengeluaran') }}</p><p class="mt-2 text-2xl font-semibold text-red-600">Rp {{ number_format($totalExpense,0,',','.') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Net Cashflow') }}</p><p class="mt-2 text-2xl font-semibold {{ $netCashflow >=0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($netCashflow,0,',','.') }}</p></div>
        <div class="bg-white shadow sm:rounded-lg p-6"><p class="text-sm text-gray-500">{{ __('Total Saldo (Aktif)') }}</p><p class="mt-2 text-2xl font-semibold">Rp {{ number_format($totalBalance,0,',','.') }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Cashflow') }}</h3>
            @if(empty($monthly) || (array_sum(array_column($monthly,'income'))==0 && array_sum(array_column($monthly,'expense'))==0))
                <p class="text-sm text-gray-400 py-8 text-center">{{ __('Belum ada data pada periode ini') }}</p>
            @else
                <canvas id="financeCashflow" height="220"></canvas>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-gray-500"><tr><th class="text-left py-1">{{ __('Bulan') }}</th><th class="text-right">{{ __('Pemasukan') }}</th><th class="text-right">{{ __('Pengeluaran') }}</th><th class="text-right">{{ __('Net') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($monthly as $m)
                                <tr><td class="py-1">{{ $m['label'] }}</td><td class="text-right text-emerald-600">Rp {{ number_format($m['income'],0,',','.') }}</td><td class="text-right text-red-600">Rp {{ number_format($m['expense'],0,',','.') }}</td><td class="text-right font-medium">Rp {{ number_format($m['net'],0,',','.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Pengeluaran per Kategori') }}</h3>
            @if($expenseByCategory->isEmpty())
                <p class="text-sm text-gray-400 py-8 text-center">{{ __('Belum ada pengeluaran') }}</p>
            @else
                <canvas id="financeCategory" height="220"></canvas>
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($expenseByCategory as $cat)
                        <li class="flex justify-between py-2 text-sm"><span class="text-gray-600">{{ $cat['name'] }}</span><span class="font-medium">Rp {{ number_format($cat['total'],0,',','.') }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-3">{{ __('Rincian Pemasukan') }} <span class="text-sm font-normal text-gray-500">({{ $incomeTransactions->count() }})</span></h3>
            @if($incomeTransactions->isEmpty())
                <p class="text-sm text-gray-400">{{ __('Tidak ada pemasukan pada periode ini') }}</p>
            @else
                <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @foreach($incomeTransactions as $tx)
                        <li class="flex justify-between py-2 text-sm"><span>{{ $tx->transaction_date->format('d M Y') }} • {{ $tx->category->name ?? '-' }} • {{ $tx->description ?? '-' }}</span><span class="font-medium text-emerald-600">Rp {{ number_format($tx->amount,0,',','.') }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-3">{{ __('Rincian Pengeluaran') }} <span class="text-sm font-normal text-gray-500">({{ $expenseTransactions->count() }})</span></h3>
            @if($expenseTransactions->isEmpty())
                <p class="text-sm text-gray-400">{{ __('Tidak ada pengeluaran pada periode ini') }}</p>
            @else
                <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @foreach($expenseTransactions as $tx)
                        <li class="flex justify-between py-2 text-sm"><span>{{ $tx->transaction_date->format('d M Y') }} • {{ $tx->category->name ?? '-' }} • {{ $tx->description ?? '-' }}</span><span class="font-medium text-red-600">Rp {{ number_format($tx->amount,0,',','.') }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('financeCashflow');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json(array_column($monthly, 'label')),
                        datasets: [
                            { label: @json(__('Pemasukan')), data: @json(array_column($monthly, 'income')), backgroundColor: 'rgba(16,185,129,0.8)' },
                            { label: @json(__('Pengeluaran')), data: @json(array_column($monthly, 'expense')), backgroundColor: 'rgba(239,68,68,0.8)' },
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
            const ctx2 = document.getElementById('financeCategory');
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
    @endpush
</x-app-layout>
