<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class ServiceRecordService
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function create(array $data): ServiceRecord
    {
        return DB::transaction(function () use ($data) {
            $createTx = ! empty($data['create_transaction']) && ! empty($data['account_id']);
            $accountId = $data['account_id'] ?? null;
            unset($data['create_transaction']);

            // Ensure total = labor + parts if not matching (allow override, but default calc)
            if (! isset($data['total_cost']) || $data['total_cost'] != ($data['labor_cost'] + $data['parts_cost'])) {
                // keep user-provided total_cost if exists, otherwise calc
                if (empty($data['total_cost'])) {
                    $data['total_cost'] = $data['labor_cost'] + $data['parts_cost'];
                }
            }

            if ($createTx) {
                $category = Category::where('user_id', $data['user_id'])->where('type', 'expense')->where('name', 'Servis')->first()
                    ?? Category::where('user_id', $data['user_id'])->where('type', 'expense')->first();
                $tx = $this->transactionService->create([
                    'user_id' => $data['user_id'],
                    'account_id' => $accountId,
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $data['total_cost'],
                    'transaction_date' => $data['service_date'],
                    'description' => ($data['service_type'] ?? 'Servis') . ' - ' . (Vehicle::find($data['vehicle_id'])?->name ?? ''),
                    'notes' => $data['notes'] ?? null,
                ]);
                $data['transaction_id'] = $tx->id;
                $data['account_id'] = $accountId;
            }

            $record = ServiceRecord::create($data);

            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle && $record->odometer > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $record->odometer]);
            }

            return $record;
        });
    }

    public function update(ServiceRecord $record, array $data): ServiceRecord
    {
        return DB::transaction(function () use ($record, $data) {
            if ($record->transaction_id) {
                $tx = \App\Models\Transaction::find($record->transaction_id);
                if ($tx) {
                    $this->transactionService->delete($tx);
                }
                $record->transaction_id = null;
            }

            $shouldCreateTx = ! empty($data['account_id']);
            if ($shouldCreateTx) {
                $category = Category::where('user_id', $record->user_id)->where('type', 'expense')->where('name', 'Servis')->first()
                    ?? Category::where('user_id', $record->user_id)->where('type', 'expense')->first();
                $tx = $this->transactionService->create([
                    'user_id' => $record->user_id,
                    'account_id' => $data['account_id'],
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $data['total_cost'],
                    'transaction_date' => $data['service_date'],
                    'description' => ($data['service_type'] ?? 'Servis') . ' - ' . Vehicle::find($data['vehicle_id'])?->name,
                    'notes' => $data['notes'] ?? null,
                ]);
                $data['transaction_id'] = $tx->id;
            } else {
                $data['transaction_id'] = null;
                $data['account_id'] = null;
            }

            $record->update($data);

            $vehicle = Vehicle::find($record->vehicle_id);
            if ($vehicle && $record->odometer > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $record->odometer]);
            }

            return $record;
        });
    }

    public function delete(ServiceRecord $record): void
    {
        DB::transaction(function () use ($record) {
            if ($record->transaction_id) {
                $tx = \App\Models\Transaction::find($record->transaction_id);
                if ($tx) {
                    $this->transactionService->delete($tx);
                }
            }
            $record->delete();
        });
    }
}
