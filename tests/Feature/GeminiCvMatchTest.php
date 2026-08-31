<?php

namespace Tests\Feature;

use App\Enums\CompanyStatus;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\User;
use App\Services\GeminiCvMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiCvMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_gemini_matcher_parses_strict_json(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'match_score' => 82,
                                'matched_skills' => ['Laravel', 'PHP'],
                                'missing_skills' => ['Kubernetes'],
                                'summary' => 'Strong Laravel intern. Missing Kubernetes. Worth a screening call.',
                            ]),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $result = app(GeminiCvMatcher::class)->match(
            'Jane Doe Laravel PHP intern at Campus Labs',
            'We need Laravel, PHP, and Kubernetes.'
        );

        $this->assertSame(82, $result['match_score']);
        $this->assertSame(['Laravel', 'PHP'], $result['matched_skills']);
        $this->assertSame(['Kubernetes'], $result['missing_skills']);
        $this->assertSame('gemini', $result['source']);
        $this->assertStringContainsString('Worth a screening call', $result['summary']);
    }

    public function test_applying_stores_gemini_score_on_the_application(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'match_score' => 76,
                                'matched_skills' => ['Laravel'],
                                'missing_skills' => ['AWS'],
                                'summary' => 'Good internship fit with Laravel projects. Cloud experience is light.',
                            ]),
                        ]],
                    ],
                ]],
            ]),
        ]);

        [$student, $job] = $this->makeApplicantAndJob();

        $this->actingAs($student)
            ->post(route('applications.store', $job), ['cover_letter' => 'I know Laravel.'])
            ->assertRedirect();

        $application = Application::first();
        $this->assertNotNull($application);
        $this->assertSame(76, $application->match_score);
        $this->assertSame('gemini', $application->ai_match_source);
        $this->assertContains('Laravel', $application->ats_matched_keywords);
        $this->assertStringContainsString('internship fit', $application->ai_summary);
    }

    public function test_keyword_fallback_runs_when_gemini_fails(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Overloaded']], 503),
        ]);

        [$student, $job] = $this->makeApplicantAndJob();

        $this->actingAs($student)
            ->post(route('applications.store', $job), ['cover_letter' => 'Laravel work'])
            ->assertRedirect();

        $application = Application::first();
        $this->assertSame('fallback', $application->ai_match_source);
        $this->assertGreaterThan(0, $application->match_score);
        $this->assertContains('Laravel', $application->ats_matched_keywords);
    }

    /**
     * @return array{0:User,1:JobPosting}
     */
    protected function makeApplicantAndJob(): array
    {
        $recruiter = User::factory()->create([
            'role' => UserRole::Recruiter,
            'email_verified_at' => now(),
        ]);
        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => 'Nexus Labs',
            'slug' => 'nexus-labs-gemini',
            'status' => CompanyStatus::Approved,
        ]);
        $job = JobPosting::create([
            'user_id' => $recruiter->id,
            'company_id' => $company->id,
            'title' => 'Laravel Intern',
            'slug' => 'laravel-intern-gemini',
            'type' => 'internship',
            'category' => 'technology',
            'location_type' => 'remote',
            'location' => 'Remote',
            'description' => str_repeat('We need a Laravel intern for campus product work. ', 4),
            'requirements' => 'Laravel, PHP, Git',
            'skills' => ['Laravel', 'PHP'],
            'ats_keywords' => ['Laravel', 'PHP', 'Git'],
            'status' => JobStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'published_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);
        Profile::create([
            'user_id' => $student->id,
            'headline' => 'Laravel intern',
            'bio' => 'Built campus tools with Laravel and PHP.',
            'location' => 'Dhaka',
            'skills' => ['Laravel', 'PHP', 'Git'],
            'resume_text' => 'Education\nBSc CS\nExperience\nLaravel intern\nSkills\nLaravel PHP Git',
            'completeness' => 70,
        ]);

        return [$student, $job];
    }
}
