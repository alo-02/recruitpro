@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Branding')

@section('content')
<div class="mb-4">
    <p class="section-kicker text-label-md mb-1">Site identity</p>
    <h1 class="text-headline-md text-primary-rp mb-1">Logo &amp; Favicon</h1>
    <p class="text-body-md text-muted-rp mb-0">Control the mark in the header, login panel, and the browser tab icon.</p>
</div>

<form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="rp-card rp-card-lg">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label text-label-md text-muted-rp" for="site_name">Site name</label>
            <input type="text" name="site_name" id="site_name" class="form-control @error('site_name') is-invalid @enderror"
                   value="{{ old('site_name', $brand->siteName()) }}" maxlength="80" required>
            @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label text-label-md text-muted-rp" for="tagline">Tagline</label>
            <input type="text" name="tagline" id="tagline" class="form-control @error('tagline') is-invalid @enderror"
                   value="{{ old('tagline', $brand->tagline()) }}" maxlength="80" placeholder="Look closer">
            @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label text-label-md text-muted-rp">Logo</label>
            <p class="text-body-md text-muted-rp">PNG, JPG, WEBP or SVG. Shown in the navbar, footer, and sidebars. Square works best.</p>
            <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:var(--rp-surface-container-low);">
                @if($brand->logoUrl())
                    <img src="{{ $brand->logoUrl() }}" alt="Current logo" class="rp-logo" style="height:56px;max-width:160px;">
                @else
                    @include('partials.brand-mark', ['size' => 48, 'forceIris' => true])
                    <span class="text-body-md text-muted-rp">Default iris mark</span>
                @endif
            </div>
            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.svg,image/*">
            @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @if($brand->logoUrl())
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                    <label class="form-check-label text-body-md" for="remove_logo">Remove logo and use the iris mark</label>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label text-label-md text-muted-rp">Favicon (browser tab)</label>
            <p class="text-body-md text-muted-rp">ICO, PNG or SVG. 32×32 or 64×64 is enough. This is the small icon on the tab.</p>
            <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:var(--rp-surface-container-low);">
                <img src="{{ $brand->faviconUrl() }}" alt="Current favicon" width="32" height="32" style="object-fit:contain;background:#fff;border-radius:6px;">
                <span class="text-body-md text-muted-rp">Current tab icon</span>
            </div>
            <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept=".ico,.png,.svg,.webp,.jpg,.jpeg,image/*">
            @error('favicon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @if($brand->faviconPath())
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_favicon" value="1" id="remove_favicon">
                    <label class="form-check-label text-body-md" for="remove_favicon">Remove favicon and use the default</label>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-rp-primary">Save branding</button>
    </div>
</form>
@endsection
