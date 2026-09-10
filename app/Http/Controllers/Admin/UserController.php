<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('created_at');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
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
