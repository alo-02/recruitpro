<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfaceChangesTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_page_does_not_show_demo_accounts(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Demo Accounts')
            ->assertDontSee('student@recruitpro.com');
    }

    public function test_student_interfaces_have_home_and_dashboard_and_hide_blog(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('home'), false)
            ->assertSee(route('student.dashboard'), false)
            ->assertDontSee(route('blog.index'), false);
    }

    public function test_home_page_find_job_filters_are_fully_rendered(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find Jobs')
            ->assertSee('name="q"', false)
            ->assertSee('name="location"', false)
            ->assertSee('name="category[]"', false)
            ->assertSee('name="job_type[]"', false)
            ->assertSee('name="location_type[]"', false);
    }

    public function test_recruiter_sidebar_hides_team_stage_and_review(): void
    {
        $recruiter = User::factory()->create([
            'role' => UserRole::Recruiter,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($recruiter)
            ->get(route('recruiter.dashboard'))
            ->assertOk()
            ->assertSee(route('screening.index'), false)
            ->assertDontSee(route('team.index'), false)
            ->assertDontSee(route('stages.index'), false)
            ->assertDontSee(route('candidates.review'), false);
    }

    public function test_admin_sidebar_hides_cms_plan_and_feature_flags(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertDontSee(route('admin.cms'), false)
            ->assertDontSee(route('admin.plans'), false)
            ->assertDontSee(route('admin.flags'), false);
    }

    public function test_admin_reports_page_loads_and_pdf_downloads(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Download PDF');

        $this->actingAs($admin)
            ->get(route('admin.reports.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
