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
<h1>Laporan Kendaraan</h1>
<p>Periode: {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</p>

<table>
<tr><th>Total BBM</th><td class="text-right">Rp {{ number_format($fuelStats['total'],0,',','.') }} ({{ number_format($fuelStats['liters'],2,',','.') }} L)</td></tr>
<tr><th>Total Servis</th><td class="text-right">Rp {{ number_format($serviceStats['total'],0,',','.') }}</td></tr>
<tr><th>Total Biaya</th><td class="text-right"><strong>Rp {{ number_format($totalVehicleCost,0,',','.') }}</strong></td></tr>
@if($distance)<tr><th>Jarak</th><td class="text-right">{{ number_format($distance,0,',','.') }} km • {{ $efficiency ? number_format($efficiency,2,',','.') . ' km/L' : '' }}</td></tr>@endif
</table>

<h2>Per Kendaraan</h2>
<table>
<tr><th>Kendaraan</th><th class="text-right">BBM</th><th class="text-right">Servis</th><th class="text-right">Total</th></tr>
@foreach($perVehicle as $row)
<tr><td>{{ $row['vehicle']->name }} ({{ $row['vehicle']->license_plate }})</td><td class="text-right">Rp {{ number_format($row['fuel_total'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($row['service_total'],0,',','.') }}</td><td class="text-right">Rp {{ number_format($row['total'],0,',','.') }}</td></tr>
@endforeach
</table>
</body>
</html>
