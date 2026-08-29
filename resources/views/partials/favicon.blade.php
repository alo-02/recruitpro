<link rel="icon" href="{{ $brand->faviconUrl() }}" type="{{ str_ends_with($brand->faviconUrl(), '.svg') ? 'image/svg+xml' : 'image/png' }}">
<link rel="apple-touch-icon" href="{{ $brand->logoUrl() ?? $brand->faviconUrl() }}">
