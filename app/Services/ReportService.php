<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FuelRecord;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Support\GroupsByMonth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    use GroupsByMonth;

    public function finance(int $userId, ?string $startDate, ?string $endDate, bool $withDetails = true): array
    {
        [$start, $end] = $this->resolveRange($startDate, $endDate);

        $base = Transaction::where('user_id', $userId)->whereBetween('transaction_date', [$start, $end]);

        $totalIncome = (clone $base)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $base)->where('type', 'expense')->sum('amount');
        $netCashflow = $totalIncome - $totalExpense;

        $totalBalance = Account::where('user_id', $userId)->where('is_active', true)->sum('current_balance');

        $expenseByCategory = (clone $base)->where('type', 'expense')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['name' => $r->category->name ?? __('Tanpa Kategori'), 'total' => (float) $r->total]);

        // Cashflow monthly breakdown within range
        $monthly = $withDetails ? $this->monthlyCashflow($userId, $start, $end) : [];

        // Income/Expense lists for detail
        $incomeTransactions = $withDetails
            ? (clone $base)->where('type', 'income')->with(['category', 'account'])->orderByDesc('transaction_date')->get()
            : collect();
        $expenseTransactions = $withDetails
            ? (clone $base)->where('type', 'expense')->with(['category', 'account'])->orderByDesc('transaction_date')->get()
            : collect();

        return compact('start', 'end', 'totalIncome', 'totalExpense', 'netCashflow', 'totalBalance', 'expenseByCategory', 'monthly', 'incomeTransactions', 'expenseTransactions');
    }

    public function vehicle(int $userId, ?string $startDate, ?string $endDate, ?int $vehicleId = null): array
    {
        [$start, $end] = $this->resolveRange($startDate, $endDate);

        $fuelQuery = FuelRecord::where('user_id', $userId)->whereBetween('fuel_date', [$start, $end]);
        $serviceQuery = ServiceRecord::where('user_id', $userId)->whereBetween('service_date', [$start, $end]);

        if ($vehicleId) {
            $fuelQuery->where('vehicle_id', $vehicleId);
            $serviceQuery->where('vehicle_id', $vehicleId);
        }

        $fuelStats = [
            'total' => (float) $fuelQuery->sum('total_cost'),
            'liters' => (float) $fuelQuery->sum('liters'),
            'count' => (int) $fuelQuery->count(),
            'avg_price' => 0,
        ];
        if ($fuelStats['liters'] > 0) {
            $fuelStats['avg_price'] = $fuelStats['total'] / $fuelStats['liters'];
        }

        $serviceStats = [
            'total' => (float) $serviceQuery->sum('total_cost'),
            'count' => (int) $serviceQuery->count(),
        ];

        $totalVehicleCost = $fuelStats['total'] + $serviceStats['total'];

        // Per vehicle breakdown (2 agregat query, bukan 5 query per kendaraan)
        $fuelAgg = FuelRecord::where('user_id', $userId)->whereBetween('fuel_date', [$start, $end])
            ->selectRaw('vehicle_id, SUM(total_cost) as fuel_total, SUM(liters) as fuel_liters, COUNT(*) as fuel_count')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $serviceAgg = ServiceRecord::where('user_id', $userId)->whereBetween('service_date', [$start, $end])
            ->selectRaw('vehicle_id, SUM(total_cost) as service_total, COUNT(*) as service_count')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $perVehicle = Vehicle::where('user_id', $userId)->get()->map(function ($v) use ($fuelAgg, $serviceAgg) {
            $fuel = $fuelAgg->get($v->id);
            $service = $serviceAgg->get($v->id);
            $fuelTotal = $fuel ? (float) $fuel->fuel_total : 0.0;
            $serviceTotal = $service ? (float) $service->service_total : 0.0;

            return [
                'vehicle' => $v,
                'fuel_total' => $fuelTotal,
                'service_total' => $serviceTotal,
                'total' => $fuelTotal + $serviceTotal,
                'fuel_liters' => $fuel ? (float) $fuel->fuel_liters : 0.0,
                'fuel_count' => $fuel ? (int) $fuel->fuel_count : 0,
                'service_count' => $service ? (int) $service->service_count : 0,
            ];
        });

        // Distance & efficiency overall (if vehicleId specified or aggregate)
        $distance = null;
        $efficiency = null;
        $costPerKm = null;
        $odometers = FuelRecord::where('user_id', $userId)
            ->whereBetween('fuel_date', [$start, $end])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('odometer')
            ->pluck('odometer');
        if ($odometers->count() >= 2) {
            $distance = $odometers->last() - $odometers->first();
            if ($distance > 0 && $fuelStats['liters'] > 0) {
                $efficiency = $distance / $fuelStats['liters'];
                $costPerKm = $fuelStats['total'] / $distance;
            }
        }

        return compact('start', 'end', 'fuelStats', 'serviceStats', 'totalVehicleCost', 'perVehicle', 'distance', 'efficiency', 'costPerKm');
    }

    public function fuel(int $userId, ?string $startDate, ?string $endDate, ?int $vehicleId = null): array
    {
        [$start, $end] = $this->resolveRange($startDate, $endDate);

        $base = FuelRecord::where('user_id', $userId)->whereBetween('fuel_date', [$start, $end])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId));

        $stats = [
            'total' => (float) (clone $base)->sum('total_cost'),
            'liters' => (float) (clone $base)->sum('liters'),
            'count' => (int) (clone $base)->count(),
            'avg_price' => 0.0,
            'avg_cost' => 0.0,
        ];
        if ($stats['liters'] > 0) {
            $stats['avg_price'] = $stats['total'] / $stats['liters'];
        }
        if ($stats['count'] > 0) {
            $stats['avg_cost'] = $stats['total'] / $stats['count'];
        }

        $records = (clone $base)->with('vehicle')->orderByDesc('fuel_date')->orderByDesc('id')->get();

        $perVehicle = $records->groupBy('vehicle_id')->map(fn ($rows) => [
            'vehicle' => $rows->first()->vehicle,
            'count' => $rows->count(),
            'liters' => (float) $rows->sum('liters'),
            'total' => (float) $rows->sum('total_cost'),
        ])->values();

        $monthly = $this->monthlySeries($records, 'fuel_date', ['total' => 'total_cost', 'liters' => 'liters'], $start, $end);

        return compact('start', 'end', 'stats', 'perVehicle', 'monthly', 'records');
    }

    public function service(int $userId, ?string $startDate, ?string $endDate, ?int $vehicleId = null): array
    {
        [$start, $end] = $this->resolveRange($startDate, $endDate);

        $base = ServiceRecord::where('user_id', $userId)->whereBetween('service_date', [$start, $end])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId));

        $stats = [
            'total' => (float) (clone $base)->sum('total_cost'),
            'labor' => (float) (clone $base)->sum('labor_cost'),
            'parts' => (float) (clone $base)->sum('parts_cost'),
            'count' => (int) (clone $base)->count(),
            'avg_cost' => 0.0,
        ];
        if ($stats['count'] > 0) {
            $stats['avg_cost'] = $stats['total'] / $stats['count'];
        }

        $records = (clone $base)->with('vehicle')->orderByDesc('service_date')->orderByDesc('id')->get();

        $perVehicle = $records->groupBy('vehicle_id')->map(fn ($rows) => [
            'vehicle' => $rows->first()->vehicle,
            'count' => $rows->count(),
            'labor' => (float) $rows->sum('labor_cost'),
            'parts' => (float) $rows->sum('parts_cost'),
            'total' => (float) $rows->sum('total_cost'),
        ])->values();

        $monthly = $this->monthlySeries($records, 'service_date', ['total' => 'total_cost', 'labor' => 'labor_cost', 'parts' => 'parts_cost'], $start, $end);

        return compact('start', 'end', 'stats', 'perVehicle', 'monthly', 'records');
    }

    private function monthlySeries($records, string $dateAttribute, array $sums, string $start, string $end): array
    {
        $buckets = [];
        $cursor = Carbon::parse($start)->startOfMonth();
        $endC = Carbon::parse($end);
        while ($cursor->lte($endC)) {
            $row = ['label' => $cursor->format('M Y')];
            foreach (array_keys($sums) as $field) {
                $row[$field] = 0.0;
            }
            $buckets[$cursor->format('Y-m')] = $row;
            $cursor->addMonth();
            if (count($buckets) > 24) {
                break;
            }
        }

        foreach ($records as $record) {
            $key = Carbon::parse($record->{$dateAttribute})->format('Y-m');
            if (! isset($buckets[$key])) {
                continue;
            }
            foreach ($sums as $field => $attribute) {
                $buckets[$key][$field] += (float) $record->{$attribute};
            }
        }

        return array_values($buckets);
    }

    private function resolveRange(?string $startDate, ?string $endDate): array
    {
        $tz = 'Asia/Jakarta';
        if ($startDate && $endDate) {
            return [$startDate, $endDate];
        }
        // default: current month
        $now = Carbon::now($tz);

        return [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()];
    }

    private function monthlyCashflow(int $userId, string $start, string $end): array
    {
        $monthExpr = $this->monthKeySql('transaction_date');
        $rows = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, '.$monthExpr.' as ym, SUM(amount) as total')
            ->groupBy('type', DB::raw($monthExpr))
            ->get();

        $byMonth = $rows->groupBy('ym');
        $months = [];
        $cursor = Carbon::parse($start)->startOfMonth();
        $endC = Carbon::parse($end);
        while ($cursor->lte($endC)) {
            $income = 0.0;
            $expense = 0.0;
            foreach ($byMonth->get($cursor->format('Y-m'), collect()) as $row) {
                if ($row->type === 'income') {
                    $income = (float) $row->total;
                } elseif ($row->type === 'expense') {
                    $expense = (float) $row->total;
                }
            }
            $months[] = [
                'label' => $cursor->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
            $cursor->addMonth();
            if (count($months) > 24) {
                break;
            } // safety
        }

        return $months;
    }
}
