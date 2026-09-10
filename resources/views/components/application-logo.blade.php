@php
    $iconUrl = null;
    try {
        $siteIcon = \App\Models\SiteSetting::get('site_icon');
        $iconUrl = $siteIcon ? \Illuminate\Support\Facades\Storage::disk('public')->url($siteIcon) : null;
    } catch (\Throwable $e) {
        $iconUrl = null;
    }
    $siteName = \App\Models\SiteSetting::get('site_name', 'JoyTrack');
@endphp

@if($iconUrl)
    <img src="{{ $iconUrl }}" alt="{{ $siteName }}" {{ $attributes->merge(['class' => 'object-cover rounded-2xl shadow-soft']) }} />
@else
    <img src="/icons/icon-192x192.png?v=2" alt="{{ $siteName }}" {{ $attributes->merge(['class' => 'object-cover rounded-2xl shadow-soft']) }} />
@endif
