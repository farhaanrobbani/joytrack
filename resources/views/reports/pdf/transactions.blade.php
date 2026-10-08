<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
h1 { font-size: 18px; margin-bottom: 4px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
th { background: #f3f4f6; }
.text-right { text-align: right; }
</style>
</head>
<body>
<h1>Laporan Transaksi</h1>
<p>Periode: {{ $start ? \Carbon\Carbon::parse($start)->format('d M Y') : 'Semua waktu' }} — {{ $end ? \Carbon\Carbon::parse($end)->format('d M Y') : 'Semua waktu' }}</p>

<table>
<tr><th>Tanggal</th><th>Jenis</th><th>Akun</th><th>Kategori</th><th class="text-right">Nominal</th><th>Deskripsi</th></tr>
@forelse($transactions as $tx)
<tr>
    <td>{{ $tx->transaction_date->format('d M Y') }}</td>
    <td>{{ $tx->type }}</td>
    <td>{{ $tx->account->name ?? '-' }}{{ $tx->destinationAccount ? ' → '.$tx->destinationAccount->name : '' }}</td>
    <td>{{ $tx->category->name ?? '-' }}</td>
    <td class="text-right">Rp {{ number_format($tx->amount,0,',','.') }}</td>
    <td>{{ $tx->description ?? '' }}</td>
</tr>
@empty
<tr><td colspan="6">Tidak ada transaksi</td></tr>
@endforelse
</table>
<p>Total: {{ $transactions->count() }} transaksi</p>
</body>
</html>
