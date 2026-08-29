<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SiteBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function __construct(protected SiteBranding $branding) {}

    public function edit(): View
    {
        return view('admin.branding', [
            'brand' => $this->branding,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:80'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg,webp,jpg,jpeg', 'max:512'],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_favicon' => ['sometimes', 'boolean'],
        ]);

        $this->branding->put('site_name', $data['site_name']);
        $this->branding->put('tagline', $data['tagline'] ?? '');

        if ($request->boolean('remove_logo')) {
            $this->deleteStored($this->branding->logoPath());
            $this->branding->put('logo_path', null);
        }

        if ($request->boolean('remove_favicon')) {
            $this->deleteStored($this->branding->faviconPath());
            $this->branding->put('favicon_path', null);
        }

        if ($request->hasFile('logo')) {
            $this->replaceFile('logo_path', $request->file('logo'), 'logo');
        }

        if ($request->hasFile('favicon')) {
            $this->replaceFile('favicon_path', $request->file('favicon'), 'favicon');
        }

        ActivityLogger::log('site.branding_updated', null, [
            'site_name' => $data['site_name'],
        ]);

        return back()->with('success', 'Logo, favicon, and site name saved. Refresh any open tab to see the new icon.');
    }

    protected function replaceFile(string $settingKey, UploadedFile $file, string $prefix): void
    {
        $old = $settingKey === 'logo_path' ? $this->branding->logoPath() : $this->branding->faviconPath();
        $this->deleteStored($old);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $path = $file->storeAs('branding', $prefix.'-'.now()->format('YmdHis').'.'.$extension, 'public');
        $this->branding->put($settingKey, $path);
    }

    protected function deleteStored(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
