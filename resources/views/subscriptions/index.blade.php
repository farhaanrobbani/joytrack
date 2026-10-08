<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Berlangganan') }}</h2>
            <a href="{{ route('subscriptions.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah Berlangganan') }}</a>
        </div>
    </x-slot>

    <div x-data="{
        renew: null,
        withTx: @js(old('create_transaction') !== null ? old('create_transaction') == '1' : true),
        openRenew(sub) { this.renew = sub; this.withTx = true; this.$dispatch('open-modal', 'renew-subscription'); },
        init() {
            const id = @js(old('subscription_id'));
            if (id && window.JT_SUBSCRIPTIONS && window.JT_SUBSCRIPTIONS[id]) {
                this.renew = window.JT_SUBSCRIPTIONS[id];
                this.$nextTick(() => this.$dispatch('open-modal', 'renew-subscription'));
            }
        },
    }">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
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
                                <tr class="hover:bg-gray-50">
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
                                            <button type="button" class="text-emerald-600 hover:text-emerald-700 mr-2 font-medium" @click="openRenew(window.JT_SUBSCRIPTIONS[{{ $s->id }}])">{{ __('Perpanjang') }}</button>
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

        <x-modal name="renew-subscription" focusable>
            <template x-if="renew">
                <form method="POST" :action="renew.url" class="p-6">
                    @csrf
                    <input type="hidden" name="subscription_id" :value="renew.id">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1" x-text="renew.name"></h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ __('Siklus') }}: <span x-text="renew.cycle"></span> •
                        {{ __('Jatuh tempo') }}: <span x-text="renew.next"></span> →
                        <span class="font-semibold text-emerald-600" x-text="renew.preview"></span>
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="create_transaction" value="0">
                            <input id="renew_create_transaction" name="create_transaction" type="checkbox" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" x-model="withTx">
                            <x-input-label for="renew_create_transaction" :value="__('Catat sebagai pengeluaran')" class="ms-2" />
                        </div>

                        <div x-show="withTx" x-cloak class="space-y-4">
                            <div>
                                <x-input-label for="renew_account_id" :value="__('Akun')" />
                                <select id="renew_account_id" name="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" :required="withTx">
                                    <option value="">{{ __('Pilih akun') }}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="renew_amount" :value="__('Nominal')" />
                                <x-text-input id="renew_amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" />
                                <p class="mt-1 text-sm text-gray-500">{{ __('Kosongkan untuk memakai nominal langganan.') }}</p>
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="renew_notes" :value="__('Catatan')" />
                                <textarea id="renew_notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition-colors" @click="$dispatch('close-modal', 'renew-subscription')">
                                {{ __('Batal') }}
                            </button>
                            <x-primary-button type="submit" class="ms-auto">
                                {{ __('Perpanjang') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </template>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        window.JT_SUBSCRIPTIONS = @json($renewPayload);
    </script>
    @endpush
</x-app-layout>
