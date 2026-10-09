<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index');
    }

    public function toggleRole(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', __('Tidak bisa mengubah role diri sendiri.'));
        }

        $user->update(['role' => $user->role === 'admin' ? 'user' : 'admin']);

        return back()->with('status', __('Role user diperbarui.'));
    }
}
