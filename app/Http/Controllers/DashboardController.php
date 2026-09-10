<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ServiceReminderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service, protected ServiceReminderService $reminderService) {}

    public function index(): View
    {
        $data = $this->service->getData(auth()->id());
        $data['reminders'] = $this->reminderService->getReminders(auth()->id());

        return view('dashboard', $data);
    }
}
