<?php

use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Account $account;

    public string $name = '';
    public string $type = '';
    public string $credit_limit = '';
    public string $billing_day = '';
    public string $due_day = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(Account $account): void
    {
        Gate::authorize('update', $account);

        $this->account = $account;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->credit_limit = $account->credit_limit !== null ? (string) $account->credit_limit : '';
        $this->billing_day = $account->billing_day !== null ? (string) $account->billing_day : '';
        $this->due_day = $account->due_day !== null ? (string) $account->due_day : '';
        $this->description = $account->description ?? '';
        $this->is_active = (bool) $account->is_active;
    }

    public function save(): void
    {
        Gate::authorize('update', $this->account);

        $data = $this->formData();

        $validator = Validator::make($data, UpdateAccountRequest::accountRules(), UpdateAccountRequest::accountMessages());
        $validator->validate();

        $this->account->update($data);

        session()->flash('status', __('Akun berhasil diperbarui.'));
        $this->redirect(route('accounts.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'credit_limit' => $this->credit_limit !== '' ? $this->credit_limit : null,
            'billing_day' => $this->billing_day !== '' ? (int) $this->billing_day : null,
            'due_day' => $this->due_day !== '' ? (int) $this->due_day : null,
            'description' => $this->description !== '' ? $this->description : null,
            'is_active' => $this->is_active,
        ];
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<div>
    <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Akun')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="type" :value="__('Jenis Akun')" />
                    <select id="type" wire:model.live="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="bank">{{ __('Bank') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="ewallet">{{ __('E-wallet') }}</option>
                        <option value="savings">{{ __('Savings') }}</option>
                        <option value="credit">{{ __('Kartu Kredit / Paylater') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                @if ($type === 'credit')
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-lg space-y-4">
                        <p class="text-sm font-semibold text-emerald-700">{{ __('Detail Kartu Kredit / Paylater') }}</p>
                        <div>
                            <x-input-label for="credit_limit" :value="__('Limit Kredit')" />
                            <x-text-input id="credit_limit" wire:model="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="5000000" />
                            <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="billing_day" :value="__('Tgl Cetak Tagihan')" />
                                <x-text-input id="billing_day" wire:model="billing_day" type="number" min="1" max="31" class="mt-1 block w-full" placeholder="1" />
                                <x-input-error :messages="$errors->get('billing_day')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="due_day" :value="__('Jatuh Tempo')" />
                                <x-text-input id="due_day" wire:model="due_day" type="number" min="1" max="31" class="mt-1 block w-full" placeholder="10" />
                                <x-input-error :messages="$errors->get('due_day')" class="mt-2" />
                            </div>
                        </div>
                        <p class="text-sm text-emerald-700/80">{{ __('Belanja dicatat sebagai expense ke akun ini (saldo negatif = tagihan). Bayar tagihan dengan transfer dari bank ke akun ini.') }}</p>
                    </div>
                @endif

                <div>
                    <x-input-label :value="__('Saldo Awal')" />
                    <p class="mt-1 text-gray-700 font-medium">Rp {{ number_format($this->account->initial_balance, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Saldo awal tidak bisa diubah.') }}</p>
                </div>

                <div>
                    <x-input-label :value="__('Saldo Saat Ini')" />
                    <p class="mt-1 text-gray-700 font-medium">Rp {{ number_format($this->account->current_balance, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Saldo akan berubah otomatis sesuai transaksi.') }}</p>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Deskripsi')" />
                    <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded-sm border-gray-300 text-emerald-600 shadow-xs focus:ring-emerald-500" />
                    <x-input-label for="is_active" :value="__('Akun Aktif')" class="ms-2" />
                </div>
                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                <div class="flex gap-3">
                    <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        {{ __('Batal') }}
                    </a>
                    <x-primary-button type="submit" class="ms-auto">
                        {{ __('Simpan') }}
                    </x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
