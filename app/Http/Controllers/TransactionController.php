<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $service) {}

    public function index(Request $request): View
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        // Filters PRD §20
        if ($request->filled('type') && in_array($request->type, ['income', 'expense', 'transfer'])) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('account_id', $request->account_id)
                  ->orWhere('destination_account_id', $request->account_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'expense');
        if (! in_array($type, ['income', 'expense', 'transfer'])) {
            $type = 'expense';
        }
        $accounts = Account::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $categories = Category::where('user_id', auth()->id())->active()->where('type', $type)->orderBy('name')->get();

        // for transfer we need all accounts separately, for income/expense filtered categories already loaded via JS fallback
        $allCategories = Category::where('user_id', auth()->id())->active()->orderBy('name')->get()->groupBy('type');

        return view('transactions.create', compact('type', 'accounts', 'categories', 'allCategories'));
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
        $transaction->load(['account', 'destinationAccount', 'category']);

        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        $allCategories = Category::where('user_id', auth()->id())->active()->orderBy('name')->get()->groupBy('type');

        return view('transactions.edit', compact('transaction', 'accounts', 'allCategories'));
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
