<x-modal name="renew-subscription" focusable>
    <template x-if="renew">
        <form method="POST" :action="renew.url" class="p-6">
            @csrf
            @if(!empty($back))
                <input type="hidden" name="back" value="{{ $back }}">
            @endif
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
