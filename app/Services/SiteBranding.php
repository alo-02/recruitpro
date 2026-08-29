<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteBranding
{
    public const CACHE_KEY = 'site.branding';

    /**
     * @return array{site_name:string,tagline:string,logo_path:?string,favicon_path:?string}
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $stored = [];
            if (Schema::hasTable('site_settings')) {
                $stored = SiteSetting::query()->pluck('value', 'key')->all();
            }

            return array_merge($this->defaults(), $stored);
        });
    }

    public function siteName(): string
    {
        return (string) ($this->all()['site_name'] ?: 'RecruitPro');
    }

    public function tagline(): string
    {
        return (string) ($this->all()['tagline'] ?: 'Look closer');
    }

    public function logoPath(): ?string
    {
        $path = $this->all()['logo_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function faviconPath(): ?string
    {
        $path = $this->all()['favicon_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function logoUrl(): ?string
    {
        return $this->mediaUrl($this->logoPath());
    }

    public function faviconUrl(): string
    {
        return $this->mediaUrl($this->faviconPath()) ?? asset('favicon.svg');
    }

    public function put(string $key, ?string $value): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{site_name:string,tagline:string,logo_path:?string,favicon_path:?string}
     */
    protected function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'RecruitPro'),
            'tagline' => 'Look closer',
            'logo_path' => null,
            'favicon_path' => null,
        ];
    }

    protected function mediaUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return url('/media/'.$path);
    }
}
