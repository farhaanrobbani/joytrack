<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $category->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('categories.edit', $category) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Edit') }}</a>
                <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">{{ __('Kembali') }}</a>
            </div>
        </div>
    </x-slot>
    <div class="bg-white shadow sm:rounded-lg p-6 max-w-2xl">
        <dl class="space-y-3">
            <div><dt class="text-sm text-gray-500">{{ __('Nama') }}</dt><dd class="font-medium text-gray-900">{{ $category->name }}</dd></div>
            <div><dt class="text-sm text-gray-500">{{ __('Jenis') }}</dt><dd class="font-medium text-gray-900">{{ $category->type_label }}</dd></div>
            <div><dt class="text-sm text-gray-500">{{ __('Status') }}</dt><dd><span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">{{ $category->is_active ? __('Aktif') : __('Nonaktif') }}</span></dd></div>
        </dl>
    </div>
</x-app-layout>
