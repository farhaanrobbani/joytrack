@props(['label', 'value', 'icon' => null, 'trend' => null, 'color' => 'emerald'])

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-soft border border-gray-100 dark:border-gray-800 p-6 hover:shadow-soft-lg transition-shadow">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ $value }}</p>
            @if($trend)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $trend }}</p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center
                @if($color === 'emerald') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400
                @elseif($color === 'red') bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400
                @elseif($color === 'amber') bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400
                @elseif($color === 'brand') bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400
                @else bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 @endif
            ">
                <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5" />
            </div>
        @endif
    </div>
    {{ $slot }}
</div>
