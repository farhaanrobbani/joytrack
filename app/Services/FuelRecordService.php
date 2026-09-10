<?php

namespace App\Services;

use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class FuelRecordService
{
    public function __construct(
        protected TransactionService $transactionService,
        protected AccountBalanceService $balanceService
    ) {}

    public function create(array $data): FuelRecord
    {
        return DB::transaction(function () use ($data) {
            $createTx = ! empty($data['create_transaction']) && ! empty($data['account_id']);
            $accountId = $data['account_id'] ?? null;
            unset($data['create_transaction']);

            // Fuel record will reference transaction after creation if needed
            $transaction = null;
            if ($createTx) {
                $category = Category::where('user_id', $data['user_id'])
                    ->where('type', 'expense')
                    ->where('name', 'Kendaraan')
                    ->first();
                if (! $category) {
                    $category = Category::firstOrCreate(
                        ['user_id' => $data['user_id'], 'name' => 'Kendaraan', 'type' => 'expense'],
                        ['is_active' => true]
                    );
                }
                $transaction = $this->transactionService->create([
                    'user_id' => $data['user_id'],
                    'account_id' => $accountId,
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $data['total_cost'],
                    'transaction_date' => $data['fuel_date'],
                    'description' => 'BBM ' . ($data['fuel_type'] ?? '') . ' - ' . ($data['vehicle_id'] ? Vehicle::find($data['vehicle_id'])?->name ?? '' : ''),
                    'notes' => $data['notes'] ?? null,
                ]);
                $data['transaction_id'] = $transaction->id;
                $data['account_id'] = $accountId;
            }

            $fuel = FuelRecord::create($data);

            // Update vehicle odometer if greater
            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle && $fuel->odometer > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $fuel->odometer]);
            }

            return $fuel;
        });
    }

    public function update(FuelRecord $fuel, array $data): FuelRecord
    {
        return DB::transaction(function () use ($fuel, $data) {
            $oldTransactionId = $fuel->transaction_id;

            // If fuel had transaction, we need to update or delete it when total_cost or account changes
            // Simplified: delete old transaction and recreate if account_id present? But we keep logic to adjust balance via TransactionService
            if ($oldTransactionId) {
                $oldTx = \App\Models\Transaction::find($oldTransactionId);
                if ($oldTx) {
                    $this->transactionService->delete($oldTx);
                }
                $fuel->transaction_id = null;
            }

            // Determine if new transaction should be created (if account_id present, consider it as create)
            $shouldCreateTx = ! empty($data['account_id']);
            $newTransaction = null;
            if ($shouldCreateTx) {
                $category = Category::where('user_id', $fuel->user_id)->where('type', 'expense')->where('name', 'Kendaraan')->first()
                    ?? Category::firstOrCreate(['user_id' => $fuel->user_id, 'name' => 'Kendaraan', 'type' => 'expense'], ['is_active' => true]);
                $newTransaction = $this->transactionService->create([
                    'user_id' => $fuel->user_id,
                    'account_id' => $data['account_id'],
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $data['total_cost'],
                    'transaction_date' => $data['fuel_date'],
                    'description' => 'BBM ' . ($data['fuel_type'] ?? '') . ' - ' . Vehicle::find($data['vehicle_id'])?->name,
                    'notes' => $data['notes'] ?? null,
                ]);
                $data['transaction_id'] = $newTransaction->id;
            } else {
                $data['transaction_id'] = null;
                $data['account_id'] = null;
            }

            $fuel->update($data);

            $vehicle = Vehicle::find($fuel->vehicle_id);
            if ($vehicle && $fuel->odometer > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $fuel->odometer]);
            }

            return $fuel;
        });
    }

    public function delete(FuelRecord $fuel): void
    {
        DB::transaction(function () use ($fuel) {
            if ($fuel->transaction_id) {
                $tx = \App\Models\Transaction::find($fuel->transaction_id);
                if ($tx) {
                    $this->transactionService->delete($tx);
                }
            }
            $fuel->delete();
        });
    }

    public function stats(int $userId, ?int $vehicleId = null): array
    {
        $query = FuelRecord::where('user_id', $userId);
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
        $records = $query->orderBy('fuel_date')->orderBy('odometer')->get();

        $totalLiters = (float) $records->sum('liters');
        $totalCost = (float) $records->sum('total_cost');
        $count = $records->count();
        $avgPrice = $totalLiters > 0 ? $totalCost / $totalLiters : 0;
        $avgCost = $count > 0 ? $totalCost / $count : 0;

        // Distance and efficiency based on odometer diff
        $distance = null;
        $efficiency = null;
        $costPerKm = null;
        if ($count >= 2) {
            $first = $records->first()->odometer;
            $last = $records->last()->odometer;
            $distance = $last - $first;
            if ($distance > 0) {
                $efficiency = $distance / $totalLiters;
                $costPerKm = $totalCost / $distance;
            }
        }

        return compact('totalLiters', 'totalCost', 'avgPrice', 'avgCost', 'count', 'distance', 'efficiency', 'costPerKm');
    }
}
