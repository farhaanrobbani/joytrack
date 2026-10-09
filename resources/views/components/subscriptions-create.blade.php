<?php

use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $amount = '';
    public string $renewal_cycle = 'monthly';
    public string $next_renewal_date = '';
    public string $reminder_days = '7';
    public string $notes = '';
    public bool $is_active = true;

    public function save(): void
    {
        $data = $this->formData();

        $validator = Validator::make($data, StoreSubscriptionRequest::subscriptionRules());
        $validator->validate();

        $data['user_id'] = auth()->id();
        Subscription::create($data);

        session()->flash('status', __('Berlangganan berhasil dibuat.'));
        $this->redirect(route('subscriptions.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'amount' => $this->amount !== '' ? $this->amount : null,
            'renewal_cycle' => $this->renewal_cycle,
            'next_renewal_date' => $this->next_renewal_date,
            'reminder_days' => $this->reminder_days,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'is_active' => $this->is_active,
        ];
    }

    public function render()
    {
        return $this->view(['cycleLabels' => Subscription::CYCLE_LABELS]);
    }
};
?>

<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Berlangganan')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="{{ __('Netflix') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="amount" :value="__('Nominal (opsional)')" />
                    <x-text-input id="amount" wire:model="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Biaya per periode, hanya informatif.') }}</p>
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="renewal_cycle" :value="__('Siklus Perpanjangan')" />
                    <select id="renewal_cycle" wire:model="renewal_cycle" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        @foreach($cycleLabels as $value => $label)
                            <option wire:key="cycle-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Dipakai tombol Perpanjang untuk menghitung tanggal berikutnya.') }}</p>
                    <x-input-error :messages="$errors->get('renewal_cycle')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="next_renewal_date" :value="__('Tanggal Perpanjangan Berikutnya')" />
                    <x-text-input id="next_renewal_date" wire:model="next_renewal_date" type="date" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('next_renewal_date')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="reminder_days" :value="__('Ingatkan Sebelum (hari)')" />
                    <x-text-input id="reminder_days" wire:model="reminder_days" type="number" min="1" max="90" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('reminder_days')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Catatan')" />
                    <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" />
                    <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                </div>
                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                <div class="flex gap-3">
                    <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
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
