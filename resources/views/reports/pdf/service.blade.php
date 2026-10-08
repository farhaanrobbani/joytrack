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
<h1>Laporan Servis</h1>
<p>Periode: {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</p>

<table>
<tr><th>Total Biaya</th><td class="text-right">Rp {{ number_format($stats['total'],0,',','.') }}</td></tr>
<tr><th>Biaya Jasa</th><td class="text-right">Rp {{ number_format($stats['labor'],0,',','.') }}</td></tr>
<tr><th>Biaya Sparepart</th><td class="text-right">Rp {{ number_format($stats['parts'],0,',','.') }}</td></tr>
<tr><th>Jumlah Servis</th><td class="text-right">{{ $stats['count'] }}</td></tr>
</table>

<h2>Biaya per Bulan</h2>
<table>
<tr><th>Bulan</th><th class="text-right">Total</th><th class="text-right">Jasa</th><th class="text-right">Sparepart</th></tr>
@forelse($monthly as $m)
<tr><td>{{ $m['label'] }}</td><td class="text-right">Rp {{ number_format($m['total'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($m['labor'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($m['parts'],0,',','.') }}</td></tr>
@empty
<tr><td colspan="4">Tidak ada data</td></tr>
@endforelse
</table>

<h2>Per Kendaraan</h2>
<table>
<tr><th>Kendaraan</th><th class="text-right">Servis</th><th class="text-right">Jasa</th><th class="text-right">Sparepart</th><th class="text-right">Total</th></tr>
@forelse($perVehicle as $row)
<tr><td>{{ $row['vehicle']->name ?? '-' }}</td><td class="text-right">{{ $row['count'] }}</td><td class="text-right">Rp {{ number_format($row['labor'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($row['parts'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($row['total'],0,',','.') }}</td></tr>
@empty
<tr><td colspan="5">Tidak ada data</td></tr>
@endforelse
</table>

<h2>Riwayat Servis</h2>
<table>
<tr><th>Tanggal</th><th>Kendaraan</th><th>Jenis</th><th>Bengkel</th><th class="text-right">Jasa</th><th class="text-right">Sparepart</th><th class="text-right">Total</th></tr>
@forelse($records as $r)
<tr><td>{{ $r->service_date->format('d M Y') }}</td><td>{{ $r->vehicle->name ?? '-' }}</td><td>{{ $r->service_type }}</td><td>{{ $r->workshop ?? '-' }}</td><td class="text-right">Rp {{ number_format($r->labor_cost,0,',','.') }}</td><td class="text-right">Rp {{ number_format($r->parts_cost,0,',','.') }}</td><td class="text-right">Rp {{ number_format($r->total_cost,0,',','.') }}</td></tr>
@empty
<tr><td colspan="7">Tidak ada data</td></tr>
@endforelse
</table>
</body>
</html>
