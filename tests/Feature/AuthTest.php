<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_register_as_student(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'newstudent@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'newstudent@test.com', 'role' => 'student']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@test.com',
            'password' => 'password',
            'role' => UserRole::Student,
        ]);

        $this->post('/login', [
            'email' => 'login@test.com',
            'password' => 'password',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_student_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }

    public function test_recruiter_cannot_access_student_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Recruiter]);

        $this->actingAs($user)->get('/student/dashboard')->assertStatus(403);
    }
}
