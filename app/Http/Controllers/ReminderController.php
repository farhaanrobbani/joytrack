<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(): View
    {
        return view('reminders.index');
    }
}
