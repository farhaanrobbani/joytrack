<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $activeUsers = User::count(); // for now all counted as active; could filter verified or last activity
        $adminUsers = User::where('role', 'admin')->count();
        $recentUsers = User::orderByDesc('created_at')->limit(5)->get();

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'adminUsers', 'recentUsers'));
    }
}
