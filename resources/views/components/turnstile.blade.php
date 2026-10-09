@props([])

@if (config('turnstile.site_key'))
    <div {{ $attributes->merge(['class' => 'cf-turnstile mt-4']) }} data-sitekey="{{ config('turnstile.site_key') }}" data-theme="auto"></div>
    <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
@endif
