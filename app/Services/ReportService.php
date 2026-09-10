<?php

namespace App\Services;

use App\Models\FuelRecord;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function finance(int $userId, ?string $startDate, ?string $endDate): array
    {
        [$start, $end] = $this->resolveRange($startDate, $endDate);

        $base = Transaction::where('user_id', $userId)->whereBetween('transaction_date', [$start, $end]);

        $totalIncome = (clone $base)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $base)->where('type', 'expense')->sum('amount');
        $netCashflow = $totalIncome - $totalExpense;

        $totalBalance = \App\Models\Account::where('user_id', $userId)->where('is_active', true)->sum('current_balance');

        $expenseByCategory = (clone $base)->where('type', 'expense')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['name' => $r->category->name ?? __('Tanpa Kategori'), 'total' => (float) $r->total]);

        // Cashflow monthly breakdown within range
        $monthly = $this->monthlyCashflow($userId, $start, $end);

        // Income/Expense lists for detail
        $incomeTransactions = (clone $base)->where('type', 'income')->with(['category', 'account'])->orderByDesc('transaction_date')->get();
        $expenseTransactions = (clone $base)->where('type', 'expense')->with(['category', 'account'])->orderByDesc('transaction_date')->get();

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

        // Per vehicle breakdown
        $perVehicle = \App\Models\Vehicle::where('user_id', $userId)->get()->map(function ($v) use ($start, $end) {
            $fuel = FuelRecord::where('user_id', $v->user_id)->where('vehicle_id', $v->id)->whereBetween('fuel_date', [$start, $end]);
            $service = ServiceRecord::where('user_id', $v->user_id)->where('vehicle_id', $v->id)->whereBetween('service_date', [$start, $end]);
            $fuelTotal = (float) $fuel->sum('total_cost');
            $serviceTotal = (float) $service->sum('total_cost');
            return [
                'vehicle' => $v,
                'fuel_total' => $fuelTotal,
                'service_total' => $serviceTotal,
                'total' => $fuelTotal + $serviceTotal,
                'fuel_liters' => (float) FuelRecord::where('vehicle_id', $v->id)->whereBetween('fuel_date', [$start, $end])->sum('liters'),
                'fuel_count' => (int) FuelRecord::where('vehicle_id', $v->id)->whereBetween('fuel_date', [$start, $end])->count(),
                'service_count' => (int) ServiceRecord::where('vehicle_id', $v->id)->whereBetween('service_date', [$start, $end])->count(),
            ];
        });

        // Distance & efficiency overall (if vehicleId specified or aggregate)
        $distance = null;
        $efficiency = null;
        $costPerKm = null;
        $fuelForDistance = FuelRecord::where('user_id', $userId)->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))->orderBy('odometer')->get();
        if ($fuelForDistance->count() >= 2) {
            $distance = $fuelForDistance->last()->odometer - $fuelForDistance->first()->odometer;
            $liters = (float) $fuelForDistance->sum('liters');
            if ($distance > 0 && $liters > 0) {
                $efficiency = $distance / $liters;
                $costPerKm = $fuelStats['total'] / $distance;
            }
        }

        return compact('start', 'end', 'fuelStats', 'serviceStats', 'totalVehicleCost', 'perVehicle', 'distance', 'efficiency', 'costPerKm');
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
        $startC = Carbon::parse($start);
        $endC = Carbon::parse($end);
        $months = [];
        $cursor = $startC->copy()->startOfMonth();
        while ($cursor->lte($endC)) {
            $mStart = $cursor->copy()->startOfMonth()->toDateString();
            $mEnd = $cursor->copy()->endOfMonth()->toDateString();
            $clippedStart = max($mStart, $start);
            $clippedEnd = min($mEnd, $end);
            $income = Transaction::where('user_id', $userId)->where('type', 'income')->whereBetween('transaction_date', [$clippedStart, $clippedEnd])->sum('amount');
            $expense = Transaction::where('user_id', $userId)->where('type', 'expense')->whereBetween('transaction_date', [$clippedStart, $clippedEnd])->sum('amount');
            $months[] = [
                'label' => $cursor->format('M Y'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'net' => (float) ($income - $expense),
            ];
            $cursor->addMonth();
            if (count($months) > 24) break; // safety
        }
        return $months;
    }
}
