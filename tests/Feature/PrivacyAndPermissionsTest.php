<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_privacy_data(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('privacy.export'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_admin_can_open_permissions_matrix(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.permissions'))
            ->assertOk();
    }
}
