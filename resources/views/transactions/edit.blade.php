<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Transaksi') }}</h2></x-slot>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('transactions.update', $transaction) }}" x-data="{ type: '{{ old('type', $transaction->type) }}' }">
                @csrf @method('PATCH')
                <div class="space-y-6">
                    <div>
                        <x-input-label for="type" :value="__('Jenis Transaksi')" />
                        <select id="type" name="type" x-model="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="income" @selected(old('type', $transaction->type)==='income')>{{ __('Pemasukan') }}</option>
                            <option value="expense" @selected(old('type', $transaction->type)==='expense')>{{ __('Pengeluaran') }}</option>
                            <option value="transfer" @selected(old('type', $transaction->type)==='transfer')>{{ __('Transfer') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="account_id" :value="__('Akun')" />
                        <select id="account_id" name="account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('account_id', $transaction->account_id)==$acc->id)>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                    </div>

                    <div x-show="type === 'transfer'">
                        <x-input-label for="destination_account_id" :value="__('Akun Tujuan')" />
                        <select id="destination_account_id" name="destination_account_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Pilih Akun Tujuan') }}</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('destination_account_id', $transaction->destination_account_id)==$acc->id)>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('destination_account_id')" class="mt-2" />
                    </div>

                    <div x-show="type !== 'transfer'">
                        <x-input-label for="category_id" :value="__('Kategori')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                            <option value="">{{ __('Pilih Kategori') }}</option>
                            @php $cats = $allCategories[old('type', $transaction->type)] ?? collect(); @endphp
                            @foreach($allCategories as $typeKey => $list)
                                @foreach($list as $cat)
                                    <option value="{{ $cat->id }}" data-type="{{ $typeKey }}" class="cat-opt-{{ $typeKey }}" @selected(old('category_id', $transaction->category_id)==$cat->id)>{{ $cat->name }} ({{ $typeKey }})</option>
                                @endforeach
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400" x-text="type === 'income' ? '{{ __('Kategori pemasukan') }}' : '{{ __('Kategori pengeluaran') }}'"></p>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" :value="__('Nominal')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $transaction->amount)" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="transaction_date" :value="__('Tanggal')" />
                        <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full" :value="old('transaction_date', $transaction->transaction_date->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $transaction->description)" />
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">{{ old('notes', $transaction->notes) }}</textarea>
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
</x-app-layout>
