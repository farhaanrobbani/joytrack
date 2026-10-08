<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">{{ __('Pengingat') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Servis, dokumen kadaluarsa, dan perpanjangan berlangganan') }}</p>
            </div>
        </div>
    </x-slot>

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
            <a href="{{ route('reminders.index', array_filter(['status' => $value !== 'all' ? $value : null])) }}"
               class="px-4 py-2 rounded-xl text-sm font-medium border {{ $status === $value ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
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
                        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
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
                        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
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
                        <div class="bg-white dark:bg-gray-800 shadow-soft rounded-2xl border p-4 {{ $r['status']==='overdue' ? 'border-red-200 dark:border-red-800' : 'border-amber-200 dark:border-amber-800' }}">
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
</x-app-layout>
