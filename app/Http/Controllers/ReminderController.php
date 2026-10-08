<?php

namespace App\Http\Controllers;

use App\Services\ExpiryReminderService;
use App\Services\ServiceReminderService;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function __construct(
        protected ServiceReminderService $serviceReminders,
        protected ExpiryReminderService $expiryReminders,
    ) {}

    public function index(): View
    {
        $status = request()->query('status', 'all');
        if (! in_array($status, ['all', 'overdue', 'due_soon'], true)) {
            $status = 'all';
        }

        $services = $this->serviceReminders->getReminders(auth()->id());
        $documents = $this->expiryReminders->getDocumentReminders(auth()->id());
        $subscriptions = $this->expiryReminders->getSubscriptionReminders(auth()->id());

        $filter = fn ($items) => $status === 'all'
            ? $items
            : $items->filter(fn ($item) => $item['status'] === $status)->values();

        $services = $filter($services);
        $documents = $filter($documents);
        $subscriptions = $filter($subscriptions);

        $all = $services->merge($documents)->merge($subscriptions);

        return view('reminders.index', [
            'services' => $services,
            'documents' => $documents,
            'subscriptions' => $subscriptions,
            'status' => $status,
            'overdueCount' => $all->filter(fn ($item) => $item['status'] === 'overdue')->count(),
            'dueSoonCount' => $all->filter(fn ($item) => $item['status'] === 'due_soon')->count(),
            'totalCount' => $all->count(),
        ]);
    }
}
