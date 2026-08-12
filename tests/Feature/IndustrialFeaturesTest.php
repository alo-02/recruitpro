<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyStatus;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustrialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_endpoint(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'api@test.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_student_can_save_job(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);
        $job = $this->makeJob();

        $this->actingAs($student)
            ->post(route('jobs.save', $job))
            ->assertRedirect();

        $this->assertDatabaseHas('saved_jobs', [
            'user_id' => $student->id,
            'job_posting_id' => $job->id,
        ]);
    }

    public function test_recruiter_can_update_pipeline_via_json(): void
    {
        $recruiter = User::factory()->create([
            'role' => UserRole::Recruiter,
            'email_verified_at' => now(),
        ]);
        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => 'Acme',
            'slug' => 'acme',
            'status' => CompanyStatus::Approved,
        ]);
        $job = JobPosting::create([
            'user_id' => $recruiter->id,
            'company_id' => $company->id,
            'title' => 'Intern',
            'slug' => 'intern-role',
            'type' => 'internship',
            'location_type' => 'remote',
            'description' => str_repeat('Description ', 10),
            'status' => JobStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'published_at' => now(),
        ]);
        $student = User::factory()->create(['role' => UserRole::Student]);
        $application = Application::create([
            'user_id' => $student->id,
            'job_posting_id' => $job->id,
            'status' => ApplicationStatus::Applied,
            'applied_at' => now(),
        ]);

        $this->actingAs($recruiter)
            ->patchJson(route('applications.pipeline', $application), [
                'status' => ApplicationStatus::Screening->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'screening');
    }

    protected function makeJob(): JobPosting
    {
        $recruiter = User::factory()->create(['role' => UserRole::Recruiter]);
        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => 'Co',
            'slug' => 'co-'.uniqid(),
            'status' => CompanyStatus::Approved,
        ]);

        return JobPosting::create([
            'user_id' => $recruiter->id,
            'company_id' => $company->id,
            'title' => 'Role',
            'slug' => 'role-'.uniqid(),
            'type' => 'full-time',
            'location_type' => 'remote',
            'description' => str_repeat('Job description text ', 8),
            'status' => JobStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'published_at' => now(),
        ]);
    }
}
