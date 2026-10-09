<?php

use App\Http\Requests\RenewSubscriptionRequest;
use App\Models\Account;
use App\Models\Subscription;
use App\Services\ExpiryReminderService;
use App\Services\ServiceReminderService;
use App\Services\SubscriptionRenewalService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'status')]
    public string $status = 'all';

    public bool $renewOpen = false;

    #[Locked]
    public ?Subscription $renewSubscription = null;

    public string $renewPreview = '';
    public bool $renewCreateTransaction = true;
    public string $renewAccountId = '';
    public string $renewAmount = '';
    public string $renewNotes = '';

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['all', 'overdue', 'due_soon'], true) ? $status : 'all';
    }

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
        $this->redirect(route('reminders.index'));
    }

    public function render()
    {
        if (! in_array($this->status, ['all', 'overdue', 'due_soon'], true)) {
            $this->status = 'all';
        }

        $services = app(ServiceReminderService::class)->getReminders(auth()->id());
        $documents = app(ExpiryReminderService::class)->getDocumentReminders(auth()->id());
        $subscriptions = app(ExpiryReminderService::class)->getSubscriptionReminders(auth()->id());

        $all = $services->merge($documents)->merge($subscriptions);

        $overdueCount = $all->filter(fn ($item) => $item['status'] === 'overdue')->count();
        $dueSoonCount = $all->filter(fn ($item) => $item['status'] === 'due_soon')->count();
        $totalCount = $all->count();

        $filter = fn ($items) => $this->status === 'all'
            ? $items
            : $items->filter(fn ($item) => $item['status'] === $this->status)->values();

        $services = $filter($services);
        $documents = $filter($documents);
        $subscriptions = $filter($subscriptions);

        return $this->view([
            'services' => $services,
            'documents' => $documents,
            'subscriptions' => $subscriptions,
            'overdueCount' => $overdueCount,
            'dueSoonCount' => $dueSoonCount,
            'totalCount' => $totalCount,
            'accounts' => Account::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total Pengingat Aktif') }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 border border-red-100 dark:border-red-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-500">{{ __('Terlambat') }}</p>
            <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $overdueCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 border border-amber-100 dark:border-amber-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-500">{{ __('Segera Jatuh Tempo') }}</p>
            <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $dueSoonCount }}</p>
        </div>
    </div>

    <div class="flex gap-2 mb-6">
        @foreach(['all' => __('Semua'), 'overdue' => __('Terlambat'), 'due_soon' => __('Segera')] as $value => $label)
            <button type="button" wire:click="setStatus('{{ $value }}')" wire:key="filter-{{ $value }}"
               class="px-4 py-2 rounded-xl text-sm font-medium border {{ $status === $value ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @php($statusLabel = fn ($s) => $s === 'overdue' ? __('Terlambat') : __('Segera'))

    <div class="space-y-6">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-wrench class="w-5 h-5 text-gray-400" />
                {{ __('Servis Kendaraan') }}
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $services->count() }}</span>
            </h3>
            @if($services->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada pengingat servis') }}</div>
            @else
                <div class="space-y-3">
                    @foreach($services as $r)
                        <div wire:key="svc-{{ $r['vehicle']->id }}" class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $r['vehicle']->name }} ({{ $r['vehicle']->license_plate ?? '-' }})
                                        <span class="ms-2 px-2 py-0.5 text-xs font-medium rounded-full {{ $r['status']==='overdue' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' }}">{{ $statusLabel($r['status']) }}</span>
                                    </p>
                                    <ul class="mt-1 list-disc list-inside text-sm text-gray-600 dark:text-gray-300">
                                        @foreach($r['messages'] as $msg)<li>{{ $msg }}</li>@endforeach
                                    </ul>
                                </div>
                                <a href="{{ route('vehicles.show', $r['vehicle']) }}" class="shrink-0 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-600">{{ __('Lihat') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-document class="w-5 h-5 text-gray-400" />
                {{ __('Dokumen') }}
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $documents->count() }}</span>
            </h3>
            @if($documents->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada pengingat dokumen') }}</div>
            @else
                <div class="space-y-3">
                    @foreach($documents as $r)
                        <div wire:key="doc-{{ $r['model']->id }}" class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $r['title'] }}
                                        <span class="ms-2 px-2 py-0.5 text-xs font-medium rounded-full {{ $r['status']==='overdue' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' }}">{{ $statusLabel($r['status']) }}</span>
                                    </p>
                                    <ul class="mt-1 list-disc list-inside text-sm text-gray-600 dark:text-gray-300">
                                        @foreach($r['messages'] as $msg)<li>{{ $msg }}</li>@endforeach
                                    </ul>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $r['subtitle'] }} • {{ $r['date']->format('d M Y') }}</p>
                                </div>
                                <a href="{{ $r['edit_url'] }}" class="shrink-0 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-600">{{ __('Lihat') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-gray-400" />
                {{ __('Berlangganan') }}
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $subscriptions->count() }}</span>
            </h3>
            @if($subscriptions->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada pengingat berlangganan') }}</div>
            @else
                <div class="space-y-3">
                    @foreach($subscriptions as $r)
                        <div wire:key="sub-{{ $r['model']->id }}" class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $r['title'] }}
                                        <span class="ms-2 px-2 py-0.5 text-xs font-medium rounded-full {{ $r['status']==='overdue' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' }}">{{ $statusLabel($r['status']) }}</span>
                                    </p>
                                    <ul class="mt-1 list-disc list-inside text-sm text-gray-600 dark:text-gray-300">
                                        @foreach($r['messages'] as $msg)<li>{{ $msg }}</li>@endforeach
                                    </ul>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $r['subtitle'] }} • {{ $r['date']->format('d M Y') }}</p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <button type="button" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700" wire:click="openRenew({{ $r['model']->id }})">{{ __('Perpanjang') }}</button>
                                    <a href="{{ $r['edit_url'] }}" class="px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-600">{{ __('Lihat') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($totalCount === 0)
            <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center mb-3">
                    <x-heroicon-o-bell-alert class="w-8 h-8 text-gray-300" />
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Tidak ada pengingat aktif') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Semua servis, dokumen, dan berlangganan Anda masih aman.') }}</p>
            </div>
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
                            <input id="renew_create_transaction" wire:model="renewCreateTransaction" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" />
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
