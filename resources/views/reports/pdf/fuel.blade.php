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
<h1>Laporan BBM</h1>
<p>Periode: {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</p>

<table>
<tr><th>Total Biaya</th><td class="text-right">Rp {{ number_format($stats['total'],0,',','.') }}</td></tr>
<tr><th>Total Liter</th><td class="text-right">{{ number_format($stats['liters'],2,',','.') }} L</td></tr>
<tr><th>Jumlah Pengisian</th><td class="text-right">{{ $stats['count'] }}</td></tr>
<tr><th>Rata-rata Harga/L</th><td class="text-right">Rp {{ number_format($stats['avg_price'],0,',','.') }}</td></tr>
</table>

<h2>Biaya per Bulan</h2>
<table>
<tr><th>Bulan</th><th class="text-right">Biaya</th><th class="text-right">Liter</th></tr>
@forelse($monthly as $m)
<tr><td>{{ $m['label'] }}</td><td class="text-right">Rp {{ number_format($m['total'],0,',','.') }}</td><td class="text-right">{{ number_format($m['liters'],2,',','.') }} L</td></tr>
@empty
<tr><td colspan="3">Tidak ada data</td></tr>
@endforelse
</table>

<h2>Per Kendaraan</h2>
<table>
<tr><th>Kendaraan</th><th class="text-right">Isi</th><th class="text-right">Liter</th><th class="text-right">Total</th></tr>
@forelse($perVehicle as $row)
<tr><td>{{ $row['vehicle']->name ?? '-' }}</td><td class="text-right">{{ $row['count'] }}</td><td class="text-right">{{ number_format($row['liters'],2,',','.') }}</td><td class="text-right">Rp {{ number_format($row['total'],0,',','.') }}</td></tr>
@empty
<tr><td colspan="4">Tidak ada data</td></tr>
@endforelse
</table>

<h2>Riwayat Pengisian</h2>
<table>
<tr><th>Tanggal</th><th>Kendaraan</th><th class="text-right">Odometer</th><th class="text-right">Liter</th><th class="text-right">Total</th></tr>
@forelse($records as $r)
<tr><td>{{ $r->fuel_date->format('d M Y') }}</td><td>{{ $r->vehicle->name ?? '-' }}</td><td class="text-right">{{ number_format($r->odometer,0,',','.') }}</td><td class="text-right">{{ number_format($r->liters,2,',','.') }}</td><td class="text-right">Rp {{ number_format($r->total_cost,0,',','.') }}</td></tr>
@empty
<tr><td colspan="5">Tidak ada data</td></tr>
@endforelse
</table>
</body>
</html>
