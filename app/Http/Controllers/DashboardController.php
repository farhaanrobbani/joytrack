<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    public function index(): View
    {
        $data = $this->service->getData(auth()->id());

        return view('dashboard', $data);
    }
}
