<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        protected AccountBalanceService $balanceService
    ) {}

    /**
     * @param array $data validated data containing: user_id, account_id, category_id, type, amount, transaction_date, description, notes, destination_account_id
     */
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            if (($data['type'] ?? null) === 'transfer') {
                $data['transfer_group_id'] = (string) Str::uuid();
                // transfer should not have category
                $data['category_id'] = null;
            }

            $tx = Transaction::create($data);
            // need relations for balance service
            $tx->load(['account', 'destinationAccount']);
            $this->balanceService->applyTransactionEffect($tx);

            return $tx;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // lock accounts involved to prevent race
            $transaction->load(['account', 'destinationAccount']);

            // 1. revert old effect
            $this->balanceService->revertTransactionEffect($transaction);

            // Preserve immutable linkage for transfer group
            if ($transaction->isTransfer() && isset($data['type']) && $data['type'] === 'transfer') {
                $data['transfer_group_id'] = $transaction->transfer_group_id;
                $data['category_id'] = null;
            } elseif ($transaction->isTransfer() && ($data['type'] ?? $transaction->type) !== 'transfer') {
                // changing away from transfer -> clear transfer fields
                $data['transfer_group_id'] = null;
                $data['destination_account_id'] = null;
            } elseif (($data['type'] ?? null) === 'transfer' && !$transaction->isTransfer()) {
                $data['transfer_group_id'] = (string) Str::uuid();
                $data['category_id'] = null;
            }

            $transaction->update($data);
            $transaction->load(['account', 'destinationAccount']);

            // 3. apply new effect
            $this->balanceService->applyTransactionEffect($transaction);

            return $transaction;
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->load(['account', 'destinationAccount']);
            $this->balanceService->revertTransactionEffect($transaction);
            $transaction->delete();
        });
    }
}
