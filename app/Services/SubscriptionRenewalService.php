<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\SubscriptionRenewal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class SubscriptionRenewalService
{
    public function __construct(protected TransactionService $transactionService) {}

    /**
     * Tanggal perpanjangan berikutnya: jika jatuh tempo masih akan datang,
     * tambah siklus dari tanggal itu; jika sudah lewat, mulai dari hari ini.
     */
    public function previewNextDate(Subscription $subscription): CarbonInterface
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $next = $subscription->next_renewal_date;
        $base = $next->gte($today) ? $next : $today;

        return $subscription->nextDateFrom($base);
    }

    /**
     * Perpanjang langganan: geser tanggal berikutnya sesuai siklus,
     * catat riwayat, dan opsional buat transaksi expense.
     *
     * @param  array{create_transaction?: bool, account_id?: int, amount?: float|int|string|null, notes?: string|null}  $data
     */
    public function renew(Subscription $subscription, array $data): SubscriptionRenewal
    {
        return DB::transaction(function () use ($subscription, $data) {
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            $previous = $subscription->next_renewal_date->copy();
            $newDate = $this->previewNextDate($subscription);

            $amount = $data['amount'] ?? $subscription->amount;
            $createTx = ! empty($data['create_transaction']) && ! empty($data['account_id']) && $amount !== null;

            $transactionId = null;

            if ($createTx) {
                $category = Category::where('user_id', $subscription->user_id)
                    ->where('type', 'expense')
                    ->where('name', 'Langganan')
                    ->first()
                    ?? Category::firstOrCreate(
                        ['user_id' => $subscription->user_id, 'name' => 'Langganan', 'type' => 'expense'],
                        ['is_active' => true]
                    );

                $tx = $this->transactionService->create([
                    'user_id' => $subscription->user_id,
                    'account_id' => $data['account_id'],
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $amount,
                    'transaction_date' => $today->toDateString(),
                    'description' => __('Perpanjangan').' - '.$subscription->name,
                    'notes' => $data['notes'] ?? null,
                ]);

                $transactionId = $tx->id;
            }

            $renewal = SubscriptionRenewal::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'renewed_at' => $today,
                'previous_date' => $previous,
                'new_date' => $newDate,
                'amount' => $amount,
                'account_id' => $createTx ? $data['account_id'] : null,
                'transaction_id' => $transactionId,
                'notes' => $data['notes'] ?? null,
            ]);

            $subscription->update(['next_renewal_date' => $newDate]);

            return $renewal;
        });
    }
}
