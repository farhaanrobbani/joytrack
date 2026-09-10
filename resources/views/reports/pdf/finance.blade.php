<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
h1 { font-size: 18px; margin-bottom: 4px; }
h2 { font-size: 14px; margin-top: 16px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
th { background: #f3f4f6; }
.text-right { text-align: right; }
</style>
</head>
<body>
<h1>Laporan Keuangan</h1>
<p>Periode: {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</p>

<table>
<tr><th>Total Pemasukan</th><td class="text-right">Rp {{ number_format($totalIncome,0,',','.') }}</td></tr>
<tr><th>Total Pengeluaran</th><td class="text-right">Rp {{ number_format($totalExpense,0,',','.') }}</td></tr>
<tr><th>Net Cashflow</th><td class="text-right">Rp {{ number_format($netCashflow,0,',','.') }}</td></tr>
<tr><th>Total Saldo</th><td class="text-right">Rp {{ number_format($totalBalance,0,',','.') }}</td></tr>
</table>

<h2>Pengeluaran per Kategori</h2>
<table>
<tr><th>Kategori</th><th class="text-right">Total</th></tr>
@forelse($expenseByCategory as $cat)
<tr><td>{{ $cat['name'] }}</td><td class="text-right">Rp {{ number_format($cat['total'],0,',','.') }}</td></tr>
@empty
<tr><td colspan="2">Tidak ada data</td></tr>
@endforelse
</table>

<h2>Cashflow Bulanan</h2>
<table>
<tr><th>Bulan</th><th class="text-right">Pemasukan</th><th class="text-right">Pengeluaran</th><th class="text-right">Net</th></tr>
@foreach($monthly as $m)
<tr><td>{{ $m['label'] }}</td><td class="text-right">Rp {{ number_format($m['income'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($m['expense'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($m['net'],0,',','.') }}</td></tr>
@endforeach
</table>
</body>
</html>
