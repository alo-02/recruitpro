<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyStatus;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Enums\ScreeningDecisionType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\ScreeningSession;
use App\Models\User;
use App\Notifications\InterviewScheduled;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CvScreeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_can_run_ai_screening_and_rank_candidates(): void
    {
        [$recruiter, $job] = $this->makeJobWithApplicants();

        $this->actingAs($recruiter)
            ->post(route('screening.store', $job), ['top_n' => 5])
            ->assertRedirect();

        $session = ScreeningSession::first();
        $this->assertNotNull($session);
        $this->assertSame(3, $session->candidates_evaluated);
        $this->assertSame(5, $session->top_n);

        $ranked = $session->results()->orderBy('rank')->get();
        $this->assertCount(3, $ranked);
        $this->assertTrue($ranked->first()->overall_score >= $ranked->last()->overall_score);
        $this->assertNotEmpty($ranked->first()->ai_explanation);
        $this->assertNotEmpty($ranked->first()->ai_recommendation);

        foreach (Application::all() as $application) {
            $this->assertNotSame(ApplicationStatus::Rejected, $application->status);
        }
    }

    public function test_ai_screening_handles_structured_cv_headings_and_bullets(): void
    {
        [$recruiter, $job] = $this->makeJobWithApplicants(medicalCv: true);

        $this->actingAs($recruiter)
            ->post(route('screening.store', $job), ['top_n' => 10])
            ->assertRedirect();

        $session = ScreeningSession::first();
        $this->assertNotNull($session);
        $this->assertGreaterThan(0, $session->candidates_evaluated);
        $this->assertNotEmpty($session->results()->first()?->ai_explanation);
    }

    public function test_ats_applications_page_loads_when_filtering_by_job(): void
    {
        [$recruiter, $job] = $this->makeJobWithApplicants();

        $this->actingAs($recruiter)
            ->get(route('recruiter.applications', [
                'job_id' => $job->id,
                'keyword' => '',
                'min_score' => '',
                'sort' => 'ats',
                'status' => '',
            ]))
            ->assertOk()
            ->assertSee('AI CV Screening')
            ->assertSee('Re-scan all CVs for this job');
    }

    public function test_space_separated_skill_blob_is_split_for_matching(): void
    {
        $list = JobPosting::parseKeywordList(
            'Patient Care Clinical Diagnosis Medical Assessment Treatment Planning'
        );

        $this->assertContains('Patient Care', $list);
        $this->assertContains('Clinical Diagnosis', $list);
        $this->assertGreaterThanOrEqual(4, count($list));
    }

    public function test_recruiter_can_override_ai_and_shortlist_without_rejecting_others(): void
    {
        [$recruiter, $job] = $this->makeJobWithApplicants();

        $this->actingAs($recruiter)->post(route('screening.store', $job), ['top_n' => 5]);
        $session = ScreeningSession::first();
        $lowest = $session->results()->orderByDesc('rank')->first();

        $this->actingAs($recruiter)
            ->post(route('screening.decide', [$session, $lowest]), [
                'decision' => ScreeningDecisionType::Shortlist->value,
                'notes' => 'Strong project work',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('screening_decisions', [
            'screening_result_id' => $lowest->id,
            'decision' => ScreeningDecisionType::Shortlist->value,
            'notes' => 'Strong project work',
        ]);

        $this->assertSame(0, Application::where('status', ApplicationStatus::Rejected)->count());
    }

    public function test_top_n_dropdown_marks_ai_recommended_candidates(): void
    {
        [$recruiter, $job] = $this->makeJobWithApplicants();
        $this->actingAs($recruiter)->post(route('screening.store', $job), ['top_n' => 15]);
        $session = ScreeningSession::first();

        $this->actingAs($recruiter)
            ->get(route('screening.show', ['session' => $session, 'top_n' => 5]))
            ->assertOk()
            ->assertSee('Top 5');

        $session->refresh();
        $this->assertSame(5, $session->top_n);
        $this->assertTrue($session->results()->where('rank', 1)->first()->ai_recommended);
    }

    public function test_interview_email_is_sent_when_candidate_is_pushed_to_interview(): void
    {
        Notification::fake();
        [$recruiter, $job] = $this->makeJobWithApplicants();
        $this->actingAs($recruiter)->post(route('screening.store', $job), ['top_n' => 5]);

        $session = ScreeningSession::first();
        $result = $session->results()->orderBy('rank')->first();

        $this->actingAs($recruiter)
            ->post(route('screening.interview', [$session, $result]), [
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'mode' => 'online',
                'location_or_link' => 'https://meet.example.com/abc',
            ])
            ->assertRedirect();

        Notification::assertSentTo($result->candidate, InterviewScheduled::class);
        $this->assertSame(ApplicationStatus::Interview, $result->application->fresh()->status);
    }

    public function test_verification_code_from_email_activates_gmail_account(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'role' => UserRole::Student,
            'email' => 'candidate.gmail.test@gmail.com',
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $code = Cache::get('email-verify-code:'.$user->id);
        $this->assertNotNull($code);

        $this->actingAs($user)
            ->post(route('verification.code'), ['code' => $code])
            ->assertRedirect(route('student.dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /**
     * @return array{0:User,1:JobPosting}
     */
    protected function makeJobWithApplicants(bool $medicalCv = false): array
    {
        $recruiter = User::factory()->create([
            'role' => UserRole::Recruiter,
            'email_verified_at' => now(),
        ]);
        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => $medicalCv ? 'City Care Hospital' : 'Nexus Labs',
            'slug' => $medicalCv ? 'city-care-hospital' : 'nexus-labs',
            'status' => CompanyStatus::Approved,
        ]);
        $job = JobPosting::create([
            'user_id' => $recruiter->id,
            'company_id' => $company->id,
            'title' => $medicalCv ? 'Medical Officer – General Medicine' : 'Laravel Engineer',
            'slug' => $medicalCv ? 'medical-officer-general-medicine' : 'laravel-engineer',
            'type' => 'full-time',
            'category' => $medicalCv ? 'healthcare' : 'technology',
            'location_type' => $medicalCv ? 'onsite' : 'remote',
            'location' => $medicalCv ? 'Dhanmondi Dhaka' : 'Remote',
            'description' => $medicalCv
                ? 'We are looking for a qualified Medical Officer to provide patient care, diagnose conditions, and maintain medical records.'
                : 'We need Laravel, PHP, and MySQL. Preferred: React. Requirements: Bachelor\'s degree, 2 years experience.',
            'requirements' => $medicalCv
                ? "MBBS degree from a recognized medical institution\nValid registration with BMDC"
                : 'Laravel, PHP, MySQL, Bachelor\'s',
            'skills' => $medicalCv
                ? ['Patient Care Clinical Diagnosis Medical Assessment Treatment Planning Patient Communication']
                : ['Laravel', 'PHP', 'MySQL'],
            'ats_keywords' => $medicalCv
                ? ['MBBS', 'BMDC Registered', 'Medical Officer', 'Patient Care']
                : ['Laravel', 'PHP', 'MySQL', 'Git'],
            'status' => JobStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'published_at' => now(),
        ]);

        $profiles = $medicalCv
            ? [
                ['name' => 'Dr Asha Rahman', 'skills' => ['Patient Care', 'MBBS'], 'headline' => 'Medical Officer'],
                ['name' => 'Dr Ben Cole', 'skills' => ['Clinical Diagnosis'], 'headline' => 'House officer'],
                ['name' => 'Cara Diaz', 'skills' => ['Marketing', 'Excel'], 'headline' => 'Business student'],
            ]
            : [
                ['name' => 'Asha Rahman', 'skills' => ['Laravel', 'PHP', 'MySQL', 'Git', 'React'], 'headline' => 'Laravel developer'],
                ['name' => 'Ben Cole', 'skills' => ['PHP', 'Git'], 'headline' => 'Backend intern'],
                ['name' => 'Cara Diaz', 'skills' => ['Marketing', 'Excel'], 'headline' => 'Business student'],
            ];

        foreach ($profiles as $row) {
            $student = User::factory()->create([
                'name' => $row['name'],
                'role' => UserRole::Student,
                'email_verified_at' => now(),
            ]);
            $resume = $medicalCv
                ? "Education\n• MBBS from Dhaka Medical College\n• BMDC Registered\nExperience\n- Medical Officer intern\nSkills\n* Patient Care\n* Clinical Diagnosis"
                : implode(' ', $row['skills']).' Laravel PHP projects Git';
            Profile::create([
                'user_id' => $student->id,
                'headline' => $row['headline'],
                'bio' => 'Built campus tools with '.$row['headline'],
                'location' => 'Dhaka',
                'skills' => $row['skills'],
                'education' => [['school' => 'State University', 'degree' => $medicalCv ? 'MBBS' : 'B.S. Computer Science', 'graduation_year' => 2026]],
                'experience' => [['title' => 'Intern', 'company' => 'Campus Labs', 'years' => '1 year', 'details' => $medicalCv ? 'Ward duty' : 'PHP work']],
                'resume_text' => $resume,
                'completeness' => 70,
            ]);
            Application::create([
                'user_id' => $student->id,
                'job_posting_id' => $job->id,
                'status' => ApplicationStatus::Applied,
                'applied_at' => now(),
                'match_score' => 0,
            ]);
        }

        return [$recruiter, $job];
    }
}
