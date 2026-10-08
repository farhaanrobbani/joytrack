<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ExpiryReminderService;
use App\Services\ServiceReminderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service,
        protected ServiceReminderService $reminderService,
        protected ExpiryReminderService $expiryReminderService,
    ) {}

    public function index(): View
    {
        $data = $this->service->getData(auth()->id());
        $data['reminders'] = $this->reminderService->getReminders(auth()->id());
        $data['expiryReminders'] = $this->expiryReminderService->getAll(auth()->id());

        return view('dashboard', $data);
    }
}
