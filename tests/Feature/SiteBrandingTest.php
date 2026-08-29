<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SiteBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_branding_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.branding.edit'))
            ->assertOk()
            ->assertSee('Logo')
            ->assertSee('Favicon');
    }

    public function test_student_cannot_change_branding(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('admin.branding.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_upload_logo_and_site_name_appears_on_home(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $logo = UploadedFile::fake()->image('campus-logo.png', 128, 128);

        $this->actingAs($admin)
            ->put(route('admin.branding.update'), [
                'site_name' => 'CampusLens',
                'tagline' => 'See the fit',
                'logo' => $logo,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $brand = app(SiteBranding::class);
        $this->assertSame('CampusLens', $brand->siteName());
        $this->assertNotNull($brand->logoUrl());
        $this->assertTrue(Storage::disk('public')->exists($brand->logoPath()));

        $this->get('/')
            ->assertOk()
            ->assertSee('CampusLens')
            ->assertSee('See the fit')
            ->assertSee('/media/branding/', false);
    }
}
