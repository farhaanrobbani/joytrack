<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    public function increase(Account $account, float|string $amount): void
    {
        // Use DB raw to avoid race conditions; lock row inside transaction caller
        $account->increment('current_balance', $amount);
        $account->refresh();
    }

    public function decrease(Account $account, float|string $amount): void
    {
        $account->decrement('current_balance', $amount);
        $account->refresh();
    }

    public function revertTransactionEffect(\App\Models\Transaction $tx): void
    {
        if ($tx->isIncome()) {
            // income added -> remove
            $this->decrease($tx->account, $tx->amount);
        } elseif ($tx->isExpense()) {
            $this->increase($tx->account, $tx->amount);
        } elseif ($tx->isTransfer()) {
            // transfer: source -=, dest += ; revert = source +=, dest -=
            if ($tx->account) {
                $this->increase($tx->account, $tx->amount);
            }
            if ($tx->destinationAccount) {
                $this->decrease($tx->destinationAccount, $tx->amount);
            }
        }
    }

    public function applyTransactionEffect(\App\Models\Transaction $tx): void
    {
        if ($tx->isIncome()) {
            $this->increase($tx->account, $tx->amount);
        } elseif ($tx->isExpense()) {
            $this->decrease($tx->account, $tx->amount);
        } elseif ($tx->isTransfer()) {
            if ($tx->account) {
                $this->decrease($tx->account, $tx->amount);
            }
            if ($tx->destinationAccount) {
                $this->increase($tx->destinationAccount, $tx->amount);
            }
        }
    }
}
