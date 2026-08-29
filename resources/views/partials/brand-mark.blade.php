@php
    $size = $size ?? 36;
    $tone = $tone ?? 'ink';
    $forceIris = $forceIris ?? false;
    $brand = $brand ?? app(\App\Services\SiteBranding::class);
@endphp
@if(! $forceIris && $brand->logoUrl())
    <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->siteName() }}" class="rp-logo" width="{{ $size }}" height="{{ $size }}" style="height: {{ $size }}px; width: auto; max-width: {{ max(48, $size * 3) }}px;">
@else
<svg class="rp-iris {{ $tone === 'light' ? 'rp-iris-light' : '' }}" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 40 40" aria-hidden="true">
    <circle class="rp-iris-sclera" cx="20" cy="20" r="18"/>
    <path class="rp-iris-lid" d="M7.5 20C13 9.5 27 9.5 32.5 20C27 30.5 13 30.5 7.5 20Z" fill="none"/>
    <circle class="rp-iris-ring" cx="20" cy="20" r="8.2"/>
    <circle class="rp-iris-pupil" cx="20" cy="20" r="3.6"/>
    <circle class="rp-iris-glint" cx="17.6" cy="17.4" r="1.15"/>
</svg>
@endif
