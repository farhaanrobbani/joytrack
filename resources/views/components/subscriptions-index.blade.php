<?php

use App\Http\Requests\RenewSubscriptionRequest;
use App\Models\Account;
use App\Models\Subscription;
use App\Services\ExpiryReminderService;
use App\Services\SubscriptionRenewalService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public bool $renewOpen = false;

    #[Locked]
    public ?Subscription $renewSubscription = null;

    public string $renewPreview = '';
    public bool $renewCreateTransaction = true;
    public string $renewAccountId = '';
    public string $renewAmount = '';
    public string $renewNotes = '';

    public function openRenew(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        Gate::authorize('update', $subscription);

        $this->renewSubscription = $subscription;
        $this->renewPreview = app(SubscriptionRenewalService::class)->previewNextDate($subscription)->format('d M Y');
        $this->renewCreateTransaction = true;
        $this->renewAccountId = '';
        $this->renewAmount = '';
        $this->renewNotes = '';
        $this->resetErrorBag();
        $this->renewOpen = true;
    }

    public function closeRenew(): void
    {
        $this->renewOpen = false;
        $this->renewSubscription = null;
        $this->resetErrorBag();
    }

    public function saveRenew(): void
    {
        if (! $this->renewSubscription) {
            return;
        }
        Gate::authorize('update', $this->renewSubscription);

        $data = [
            'create_transaction' => $this->renewCreateTransaction,
            'account_id' => $this->renewAccountId !== '' ? $this->renewAccountId : null,
            'amount' => $this->renewAmount !== '' ? $this->renewAmount : null,
            'notes' => $this->renewNotes !== '' ? $this->renewNotes : null,
        ];

        $validator = Validator::make($data, RenewSubscriptionRequest::renewRules());
        $validator->after(fn ($validator) => RenewSubscriptionRequest::checkRenewData(
            $validator,
            auth()->id(),
            $data,
            $this->renewSubscription,
        ));
        $validator->validate();

        $renewal = app(SubscriptionRenewalService::class)->renew($this->renewSubscription, $data);

        session()->flash('status', __('Berlangganan berhasil diperpanjang hingga :date.', [
            'date' => $renewal->new_date->format('d M Y'),
        ]));
        $this->redirect(route('subscriptions.index'));
    }

    public function render()
    {
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->orderBy('next_renewal_date')
            ->paginate(15);

        $reminders = app(ExpiryReminderService::class);
        $subscriptions->through(fn (Subscription $subscription) => [
            'model' => $subscription,
            'days' => $reminders->daysUntilDate($subscription->next_renewal_date),
            'status' => $reminders->statusForSubscription($subscription),
        ]);

        return $this->view([
            'subscriptions' => $subscriptions,
            'accounts' => Account::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" wire:loading.class="opacity-50">
        @if($subscriptions->isEmpty())
            <div class="p-8 text-center text-gray-500">{{ __('Belum ada berlangganan') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Nominal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Siklus') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Perpanjangan Berikutnya') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($subscriptions as $row)
                            @php($s = $row['model'])
                            <tr wire:key="sub-{{ $s->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $s->name }}
                                    @if(!$s->is_active)
                                        <span class="ms-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ __('Nonaktif') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium">{{ $s->amount !== null ? 'Rp '.number_format((float) $s->amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $s->cycle_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ $s->next_renewal_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($row['status'] === 'overdue')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ __('Terlambat :days hari', ['days' => abs($row['days'])]) }}</span>
                                    @elseif($row['days'] === 0)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Jatuh tempo hari ini') }}</span>
                                    @elseif($row['status'] === 'due_soon')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if($s->is_active)
                                        <button type="button" class="text-emerald-600 hover:text-emerald-700 mr-2 font-medium" wire:click="openRenew({{ $s->id }})">{{ __('Perpanjang') }}</button>
                                    @endif
                                    <a href="{{ route('subscriptions.edit', $s) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('subscriptions.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus berlangganan ini?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $subscriptions->links() }}</div>
        @endif
    </div>

    @if($renewOpen && $renewSubscription)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" x-data x-on:keydown.escape.window="$wire.closeRenew()">
            <div class="fixed inset-0 transform transition-all" x-on:click="$wire.closeRenew()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="relative mx-auto mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-2xl">
                <form wire:submit="saveRenew" class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $renewSubscription->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ __('Siklus') }}: {{ $renewSubscription->cycle_label }} •
                        {{ __('Jatuh tempo') }}: {{ $renewSubscription->next_renewal_date->format('d M Y') }} →
                        <span class="font-semibold text-emerald-600">{{ $renewPreview }}</span>
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input id="renew_create_transaction" wire:model="renewCreateTransaction" type="checkbox" class="rounded-sm border-gray-300 text-emerald-600 shadow-xs focus:ring-emerald-500" />
                            <x-input-label for="renew_create_transaction" :value="__('Catat sebagai pengeluaran')" class="ms-2" />
                        </div>

                        @if($renewCreateTransaction)
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="renew_account_id" :value="__('Akun')" />
                                    <select id="renew_account_id" wire:model="renewAccountId" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                                        <option value="">{{ __('Pilih akun') }}</option>
                                        @foreach($accounts as $account)
                                            <option wire:key="renew-acc-{{ $account->id }}" value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="renew_amount" :value="__('Nominal')" />
                                    <x-text-input id="renew_amount" wire:model="renewAmount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" />
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Kosongkan untuk memakai nominal langganan.') }}</p>
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="renew_notes" :value="__('Catatan')" />
                                    <textarea id="renew_notes" wire:model="renewNotes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"></textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-3 pt-2">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition-colors" wire:click="closeRenew">
                                {{ __('Batal') }}
                            </button>
                            <x-primary-button type="submit" class="ms-auto">
                                {{ __('Perpanjang') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
