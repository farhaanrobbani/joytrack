<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Document;
use App\Models\Subscription;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ExpiryReminderService
{
    public const TIMEZONE = 'Asia/Jakarta';

    public const CREDIT_REMINDER_DAYS = 7;

    /**
     * @return Collection<int, array{source: string, title: string, subtitle: string, date: CarbonInterface, days: int, status: string, messages: array<string>, edit_url: string, model: Document|Subscription|Account}>
     */
    public function getAll(int $userId): Collection
    {
        return $this->getDocumentReminders($userId)
            ->merge($this->getSubscriptionReminders($userId))
            ->merge($this->getCreditReminders($userId))
            ->sortBy(fn ($r) => [$r['status'] === 'overdue' ? 0 : 1, $r['days']])
            ->values()
            ->toBase();
    }

    /**
     * @return Collection<int, array{source: string, title: string, subtitle: string, date: CarbonInterface, days: int, status: string, messages: array<string>, edit_url: string, model: Document}>
     */
    public function getDocumentReminders(int $userId): Collection
    {
        $now = $this->now();

        $items = Document::with('vehicle')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(function (Document $document) use ($now) {
                $days = $this->daysUntil($document->expiry_date, $now);
                $status = $this->statusFor($days, $document->reminder_days);

                return [
                    'source' => 'document',
                    'title' => $document->name,
                    'subtitle' => $this->documentSubtitle($document),
                    'date' => $document->expiry_date,
                    'days' => $days,
                    'status' => $status,
                    'messages' => $this->documentMessages($document, $days, $status),
                    'edit_url' => route('documents.edit', $document),
                    'model' => $document,
                ];
            })
            ->filter(fn ($item) => $item['status'] !== 'ok')
            ->values()
            ->toBase();

        return $items;
    }

    /**
     * @return Collection<int, array{source: string, title: string, subtitle: string, date: CarbonInterface, days: int, status: string, messages: array<string>, edit_url: string, model: Subscription}>
     */
    public function getSubscriptionReminders(int $userId): Collection
    {
        $now = $this->now();

        $items = Subscription::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(function (Subscription $subscription) use ($now) {
                $days = $this->daysUntil($subscription->next_renewal_date, $now);
                $status = $this->statusFor($days, $subscription->reminder_days);

                return [
                    'source' => 'subscription',
                    'title' => $subscription->name,
                    'subtitle' => $subscription->amount !== null
                        ? 'Rp '.number_format((float) $subscription->amount, 0, ',', '.')
                        : '-',
                    'date' => $subscription->next_renewal_date,
                    'days' => $days,
                    'status' => $status,
                    'messages' => $this->subscriptionMessages($subscription, $days, $status),
                    'edit_url' => route('subscriptions.edit', $subscription),
                    'model' => $subscription,
                ];
            })
            ->filter(fn ($item) => $item['status'] !== 'ok')
            ->values()
            ->toBase();

        return $items;
    }

    /**
     * @return Collection<int, array{source: string, title: string, subtitle: string, date: CarbonInterface, days: int, status: string, messages: array<string>, edit_url: string, model: Account}>
     */
    public function getCreditReminders(int $userId): Collection
    {
        $now = $this->now();

        return Account::where('user_id', $userId)
            ->where('is_active', true)
            ->where('type', 'credit')
            ->whereNotNull('due_day')
            ->where('current_balance', '<', 0)
            ->get()
            ->map(function (Account $account) use ($now) {
                $dueDate = $now->copy()->day(min((int) $account->due_day, $now->daysInMonth()));
                $days = $this->daysUntil($dueDate, $now);
                $status = $this->statusFor($days, self::CREDIT_REMINDER_DAYS);

                return [
                    'source' => 'credit',
                    'title' => __('Tagihan :name', ['name' => $account->name]),
                    'subtitle' => $account->type_label.' • Rp '.number_format(abs((float) $account->current_balance), 0, ',', '.'),
                    'date' => $dueDate,
                    'days' => $days,
                    'status' => $status,
                    'messages' => $this->creditMessages($account, $dueDate, $days, $status),
                    'edit_url' => route('accounts.edit', $account),
                    'model' => $account,
                ];
            })
            ->filter(fn ($item) => $item['status'] !== 'ok')
            ->values()
            ->toBase();
    }

    public function statusFor(int $daysUntil, int $reminderDays): string
    {
        if ($daysUntil < 0) {
            return 'overdue';
        }

        if ($daysUntil <= $reminderDays) {
            return 'due_soon';
        }

        return 'ok';
    }

    public function daysUntilDate(CarbonInterface $date): int
    {
        return $this->daysUntil($date, $this->now());
    }

    public function statusForDocument(Document $document): string
    {
        return $this->statusFor($this->daysUntilDate($document->expiry_date), $document->reminder_days);
    }

    public function statusForSubscription(Subscription $subscription): string
    {
        return $this->statusFor($this->daysUntilDate($subscription->next_renewal_date), $subscription->reminder_days);
    }

    protected function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE)->startOfDay();
    }

    protected function daysUntil(CarbonInterface $date, Carbon $now): int
    {
        return (int) round(($date->copy()->startOfDay()->getTimestamp() - $now->getTimestamp()) / 86400);
    }

    protected function documentSubtitle(Document $document): string
    {
        $parts = [$document->type_label];

        if ($document->vehicle) {
            $parts[] = $document->vehicle->name.' ('.($document->vehicle->license_plate ?? '-').')';
        }

        return implode(' • ', $parts);
    }

    /**
     * @return array<string>
     */
    protected function documentMessages(Document $document, int $days, string $status): array
    {
        $date = $document->expiry_date->format('d M Y');

        if ($status === 'overdue') {
            return [__('Dokumen :name kedaluwarsa pada :date (terlambat :days hari)', [
                'name' => $document->name,
                'date' => $date,
                'days' => abs($days),
            ])];
        }

        if ($days === 0) {
            return [__('Dokumen :name kedaluwarsa hari ini', ['name' => $document->name])];
        }

        return [__('Dokumen :name kedaluwarsa pada :date (sisa :days hari)', [
            'name' => $document->name,
            'date' => $date,
            'days' => $days,
        ])];
    }

    /**
     * @return array<string>
     */
    protected function creditMessages(Account $account, CarbonInterface $dueDate, int $days, string $status): array
    {
        $date = $dueDate->format('d M Y');

        if ($status === 'overdue') {
            return [__('Tagihan :name jatuh tempo pada :date (terlambat :days hari)', [
                'name' => $account->name,
                'date' => $date,
                'days' => abs($days),
            ])];
        }

        if ($days === 0) {
            return [__('Tagihan :name jatuh tempo hari ini', ['name' => $account->name])];
        }

        return [__('Tagihan :name jatuh tempo pada :date (sisa :days hari)', [
            'name' => $account->name,
            'date' => $date,
            'days' => $days,
        ])];
    }

    /**
     * @return array<string>
     */
    protected function subscriptionMessages(Subscription $subscription, int $days, string $status): array
    {
        $date = $subscription->next_renewal_date->format('d M Y');

        if ($status === 'overdue') {
            return [__('Perpanjangan :name jatuh tempo pada :date (terlambat :days hari)', [
                'name' => $subscription->name,
                'date' => $date,
                'days' => abs($days),
            ])];
        }

        if ($days === 0) {
            return [__('Perpanjangan :name jatuh tempo hari ini', ['name' => $subscription->name])];
        }

        return [__('Perpanjangan :name jatuh tempo pada :date (sisa :days hari)', [
            'name' => $subscription->name,
            'date' => $date,
            'days' => $days,
        ])];
    }
}
