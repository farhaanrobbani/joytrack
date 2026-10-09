<?php

use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Subscription $subscription;

    public string $name = '';
    public string $amount = '';
    public string $renewal_cycle = 'monthly';
    public string $next_renewal_date = '';
    public string $reminder_days = '7';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(Subscription $subscription): void
    {
        Gate::authorize('update', $subscription);

        $subscription->load('renewals');

        $this->subscription = $subscription;
        $this->name = $subscription->name;
        $this->amount = $subscription->amount !== null ? (string) $subscription->amount : '';
        $this->renewal_cycle = $subscription->renewal_cycle;
        $this->next_renewal_date = $subscription->next_renewal_date->format('Y-m-d');
        $this->reminder_days = (string) $subscription->reminder_days;
        $this->notes = $subscription->notes ?? '';
        $this->is_active = (bool) $subscription->is_active;
    }

    public function save(): void
    {
        Gate::authorize('update', $this->subscription);

        $data = $this->formData();

        $validator = Validator::make($data, UpdateSubscriptionRequest::subscriptionRules());
        $validator->validate();

        $this->subscription->update($data);

        session()->flash('status', __('Berlangganan berhasil diperbarui.'));
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
        return $this->view([
            'cycleLabels' => Subscription::CYCLE_LABELS,
            'renewals' => $this->subscription->renewals,
        ]);
    }
};
?>

<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Berlangganan')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="amount" :value="__('Nominal (opsional)')" />
                    <x-text-input id="amount" wire:model="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="renewal_cycle" :value="__('Siklus Perpanjangan')" />
                    <select id="renewal_cycle" wire:model="renewal_cycle" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        @foreach($cycleLabels as $value => $label)
                            <option wire:key="cycle-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
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

    <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Riwayat Perpanjangan') }}</h3>
        @if($renewals->isEmpty())
            <p class="text-sm text-gray-500">{{ __('Belum ada riwayat perpanjangan.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tanggal Perpanjang') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jatuh Tempo Lama') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jatuh Tempo Baru') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Nominal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Transaksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($renewals as $renewal)
                            <tr wire:key="renewal-{{ $renewal->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $renewal->renewed_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $renewal->previous_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $renewal->new_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium">{{ $renewal->amount !== null ? 'Rp '.number_format((float) $renewal->amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($renewal->transaction_id)
                                        <a href="{{ route('transactions.edit', $renewal->transaction_id) }}" class="text-emerald-600 hover:text-emerald-700">{{ __('Lihat transaksi') }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
