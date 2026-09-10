@props(['active' => false, 'icon' => null])

<a {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors ' .
    ($active
        ? 'bg-emerald-600 text-white'
        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100')]) }}>
    @if ($icon)
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5 shrink-0" />
    @endif
    {{ $slot }}
</a>
