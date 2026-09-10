<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ __('Kelola Beranda') }}</h2>
    </x-slot>

    <x-card>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <x-input-label for="site_name" :value="__('Nama Website')" />
                <x-text-input id="site_name" name="site_name" type="text" class="mt-1 block w-full" :value="old('site_name', $settings['site_name'])" required />
                <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="hero_title" :value="__('Judul Hero')" />
                <x-text-input id="hero_title" name="hero_title" type="text" class="mt-1 block w-full" :value="old('hero_title', $settings['hero_title'])" required />
                <x-input-error :messages="$errors->get('hero_title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="hero_subtitle" :value="__('Subjudul Hero')" />
                <textarea id="hero_subtitle" name="hero_subtitle" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
                <x-input-error :messages="$errors->get('hero_subtitle')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="site_icon" :value="__('Icon Website (persegi, 512x512, max 2MB) — akan generate 192 & 512')" />
                    @if($settings['site_icon'])
                        <div class="mt-2 flex items-center gap-3">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['site_icon']) }}" alt="Icon" class="w-16 h-16 object-cover rounded-xl border">
                            <img src="/icons/icon-192x192.png" alt="192" class="w-12 h-12 rounded border">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="remove_site_icon" value="1" class="rounded border-gray-300 text-brand-600">
                                <span>{{ __('Hapus icon') }}</span>
                            </label>
                        </div>
                    @else
                        <div class="mt-2 flex gap-2">
                            <img src="/icons/icon-192x192.png" alt="icon" class="w-12 h-12 rounded border">
                            <span class="text-xs text-gray-400">{{ __('Icon saat ini: /icons/icon-*.png') }}</span>
                        </div>
                    @endif
                    <input id="site_icon" name="site_icon" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <x-input-error :messages="$errors->get('site_icon')" class="mt-2" />
            </div>

            <div class="flex gap-3">
                <x-primary-button type="submit">{{ __('Simpan') }}</x-primary-button>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200">{{ __('Batal') }}</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
