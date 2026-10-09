<?php

use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Transaction $transaction;

    public string $type = '';
    public string $account_id = '';
    public string $destination_account_id = '';
    public string $category_id = '';
    public string $amount = '';
    public string $transaction_date = '';
    public string $description = '';
    public string $notes = '';

    public function mount(Transaction $transaction): void
    {
        Gate::authorize('update', $transaction);

        $this->transaction = $transaction;
        $this->type = $transaction->type;
        $this->account_id = (string) $transaction->account_id;
        $this->destination_account_id = $transaction->destination_account_id !== null ? (string) $transaction->destination_account_id : '';
        $this->category_id = $transaction->category_id !== null ? (string) $transaction->category_id : '';
        $this->amount = (string) $transaction->amount;
        $this->transaction_date = $transaction->transaction_date->format('Y-m-d');
        $this->description = $transaction->description ?? '';
        $this->notes = $transaction->notes ?? '';
    }

    public function updated(string $property): void
    {
        if ($property === 'type') {
            $this->category_id = '';
            $this->resetErrorBag('category_id');
        }
    }

    public function save(): void
    {
        Gate::authorize('update', $this->transaction);

        $data = $this->formData();

        $validator = Validator::make($data, UpdateTransactionRequest::transactionRules($data), UpdateTransactionRequest::transactionMessages());
        $validator->after(fn ($validator) => UpdateTransactionRequest::checkTransactionOwnership($validator, auth()->id(), $data));
        $validator->validate();

        app(TransactionService::class)->update($this->transaction, $data);

        session()->flash('status', __('Transaksi berhasil diperbarui.'));
        $this->redirect(route('transactions.index'));
    }

    private function formData(): array
    {
        return [
            'type' => $this->type,
            'account_id' => $this->account_id,
            'destination_account_id' => $this->destination_account_id !== '' ? $this->destination_account_id : null,
            'category_id' => $this->category_id !== '' ? $this->category_id : null,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'description' => $this->description !== '' ? $this->description : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ];
    }

    public function render()
    {
        return $this->view([
            'accounts' => Account::where('user_id', auth()->id())->orderBy('name')->get(),
            'allCategories' => Category::where('user_id', auth()->id())->active()->orderBy('name')->get()->groupBy('type'),
        ]);
    }
};
?>

<div>
    <div class="bg-white shadow sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="type" :value="__('Jenis Transaksi')" />
                    <select id="type" wire:model.live="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="income">{{ __('Pemasukan') }}</option>
                        <option value="expense">{{ __('Pengeluaran') }}</option>
                        <option value="transfer">{{ __('Transfer') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="account_id" :value="__('Akun')" />
                    <select id="account_id" wire:model="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                        @foreach($accounts as $acc)
                            <option wire:key="acc-{{ $acc->id }}" value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                </div>

                @if($this->type === 'transfer')
                    <div>
                        <x-input-label for="destination_account_id" :value="__('Akun Tujuan')" />
                        <select id="destination_account_id" wire:model="destination_account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Pilih Akun Tujuan') }}</option>
                            @foreach($accounts as $acc)
                                <option wire:key="dest-{{ $acc->id }}" value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('destination_account_id')" class="mt-2" />
                    </div>
                @endif

                @if($this->type !== 'transfer')
                    <div>
                        <x-input-label for="category_id" :value="__('Kategori')" />
                        <select id="category_id" wire:model="category_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Pilih Kategori') }}</option>
                            @foreach(($allCategories[$this->type] ?? []) as $cat)
                                <option wire:key="cat-{{ $cat->id }}" value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">{{ $this->type === 'income' ? __('Kategori pemasukan') : __('Kategori pengeluaran') }}</p>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>
                @endif

                <div>
                    <x-input-label for="amount" :value="__('Nominal')" />
                    <x-text-input id="amount" wire:model="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="transaction_date" :value="__('Tanggal')" />
                    <x-text-input id="transaction_date" wire:model="transaction_date" type="date" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Deskripsi')" />
                    <x-text-input id="description" wire:model="description" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                    <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
