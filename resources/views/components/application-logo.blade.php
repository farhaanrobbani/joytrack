@php
    $siteIcon = null;
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
    <span {{ $attributes->merge(['class' => 'flex items-center justify-center bg-brand-600 text-white font-bold rounded-2xl shadow-soft']) }}>JT</span>
@endif
