<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Support\GroupsByMonth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    use GroupsByMonth;

    public function getData(int $userId): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $totalBalance = Account::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('current_balance');

        $creditDebt = abs(min(0, (float) Account::where('user_id', $userId)
            ->where('is_active', true)
            ->where('type', 'credit')
            ->sum('current_balance')));

        $assetBalance = $totalBalance + $creditDebt;

        $monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $netCashflow = $monthlyIncome - $monthlyExpense;

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with(['account', 'category', 'destinationAccount'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Income vs expense last 6 months
        $cashflowChart = $this->getCashflowChart($userId, $now);

        // Expense by category current month
        $expenseByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->category->name ?? __('Tanpa Kategori'),
                'total' => (float) $row->total,
            ]);

        return compact(
            'totalBalance',
            'assetBalance',
            'creditDebt',
            'monthlyIncome',
            'monthlyExpense',
            'netCashflow',
            'recentTransactions',
            'cashflowChart',
            'expenseByCategory'
        );
    }

    private function getCashflowChart(int $userId, Carbon $now): array
    {
        $rangeStart = $now->copy()->subMonths(5)->startOfMonth()->toDateString();
        $rangeEnd = $now->copy()->endOfMonth()->toDateString();

        $monthExpr = $this->monthKeySql('transaction_date');
        $rows = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$rangeStart, $rangeEnd])
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, '.$monthExpr.' as ym, SUM(amount) as total')
            ->groupBy('type', DB::raw($monthExpr))
            ->get();

        $byMonth = $rows->groupBy('ym');
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $labels[] = $date->format('M Y');

            $income = 0.0;
            $expense = 0.0;
            foreach ($byMonth->get($date->format('Y-m'), collect()) as $row) {
                if ($row->type === 'income') {
                    $income = (float) $row->total;
                } elseif ($row->type === 'expense') {
                    $expense = (float) $row->total;
                }
            }

            $incomeData[] = $income;
            $expenseData[] = $expense;
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }
}
