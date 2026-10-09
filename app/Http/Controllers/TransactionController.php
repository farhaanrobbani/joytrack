<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $service) {}

    public function index(): View
    {
        return view('transactions.index');
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'expense');
        if (! in_array($type, ['income', 'expense', 'transfer'], true)) {
            $type = 'expense';
        }

        return view('transactions.create', compact('type'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $this->service->create($data);

        return redirect()->route('transactions.index')->with('status', __('Transaksi berhasil dibuat.'));
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);
        $transaction->load(['account', 'destinationAccount', 'category', 'attachments']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('transactions.edit', compact('transaction'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $this->service->update($transaction, $request->validated());

        return redirect()->route('transactions.index')->with('status', __('Transaksi berhasil diperbarui.'));
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $this->service->delete($transaction);

        return redirect()->route('transactions.index')->with('status', __('Transaksi berhasil dihapus.'));
    }
}
