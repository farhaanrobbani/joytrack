@props(['items', 'title'])

<div>
    <p class="px-2 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $title }}</p>
    <ul class="space-y-1">
        @foreach ($items as $item)
            @if (Route::has($item['route']))
                <li>
                    <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['route'])" :icon="$item['icon']">
                        {{ __($item['label']) }}
                    </x-sidebar-link>
                </li>
            @endif
        @endforeach
    </ul>
</div>
