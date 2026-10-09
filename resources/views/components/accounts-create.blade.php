<?php

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $type = 'bank';
    public string $initial_balance = '0';
    public string $description = '';
    public bool $is_active = true;

    public function save(): void
    {
        $data = $this->formData();

        $validator = Validator::make($data, StoreAccountRequest::storeAccountRules(), StoreAccountRequest::storeAccountMessages());
        $validator->validate();

        Account::create([
            ...$data,
            'user_id' => auth()->id(),
            'current_balance' => $data['initial_balance'],
        ]);

        session()->flash('status', __('Akun berhasil dibuat.'));
        $this->redirect(route('accounts.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'initial_balance' => $this->initial_balance,
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
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Akun')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="type" :value="__('Jenis Akun')" />
                    <select id="type" wire:model="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="bank">{{ __('Bank') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="ewallet">{{ __('E-wallet') }}</option>
                        <option value="savings">{{ __('Savings') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="initial_balance" :value="__('Saldo Awal')" />
                    <x-text-input id="initial_balance" wire:model="initial_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Saldo saat pertama kali mencatat akun ini.') }}</p>
                    <x-input-error :messages="$errors->get('initial_balance')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Deskripsi')" />
                    <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" />
                    <x-input-label for="is_active" :value="__('Akun Aktif')" class="ms-2" />
                </div>
                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                <div class="flex gap-3">
                    <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
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
