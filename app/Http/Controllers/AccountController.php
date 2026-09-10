<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::where('user_id', auth()->id())
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('accounts.create');
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['current_balance'] = $data['initial_balance'];

        Account::create($data);

        return redirect()->route('accounts.index')
            ->with('status', __('Akun berhasil dibuat.'));
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.edit', compact('account'));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return redirect()->route('accounts.index')
            ->with('status', __('Akun berhasil diperbarui.'));
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('status', __('Akun berhasil dihapus.'));
    }
}
