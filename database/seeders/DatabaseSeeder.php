<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyStatus;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $recruiter = User::create([
            'name' => 'TechFlow HR',
            'email' => 'recruiter@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Recruiter,
            'email_verified_at' => now(),
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => 'TechFlow Systems',
            'slug' => 'techflow-systems',
            'website' => 'https://techflow.example.com',
            'description' => 'Enterprise analytics platform building next-generation tools.',
            'status' => CompanyStatus::Approved,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $student = User::create([
            'name' => 'Alex Johnson',
            'email' => 'student@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'is_active' => true,
            'is_blocked' => false,
        ]);

        Profile::create([
            'user_id' => $student->id,
            'headline' => 'CS Student at State University',
            'bio' => 'Passionate computer science student with experience in web development.',
            'phone' => '+1 (555) 123-4567',
            'location' => 'San Francisco, CA',
            'skills' => ['JavaScript', 'React', 'Python', 'SQL', 'Git'],
            'education' => [
                [
                    'school' => 'State University',
                    'degree' => 'B.S. Computer Science',
                    'graduation_year' => 2026,
                    'gpa' => '3.8',
                ],
                [
                    'school' => 'City College',
                    'degree' => 'A.S. Software Development',
                    'graduation_year' => 2024,
                    'gpa' => '3.9',
                ],
            ],
            'experience' => [
                [
                    'title' => 'Web Development Intern',
                    'company' => 'Campus Labs',
                    'years' => '2024-2025',
                    'details' => 'Built internal tools with Laravel and React.',
                ],
                [
                    'title' => 'Freelance Frontend Developer',
                    'company' => 'Self-employed',
                    'years' => '2023-2024',
                    'details' => 'Delivered responsive marketing sites for local businesses.',
                ],
            ],
            'completeness' => 80,
        ]);

        $jobs = [
            [
                'title' => 'Senior Frontend Engineer',
                'slug' => 'senior-frontend-engineer',
                'type' => 'full-time',
                'category' => 'technology',
                'location_type' => 'hybrid',
                'location' => 'San Francisco, CA',
                'salary_min' => '$140k',
                'salary_max' => '$180k / yr',
                'description' => "Join our core platform team to build next-generation user interfaces for enterprise analytics. You'll lead architectural decisions and mentor junior developers in a fast-paced environment.\n\nResponsibilities:\n- Develop React components\n- Collaborate with design teams\n- Write unit tests\n\nRequirements:\n- 5+ years experience\n- React & TypeScript expertise",
                'requirements' => '5+ years React/TypeScript experience',
                'skills' => ['React', 'TypeScript', 'Full-time'],
                'ats_keywords' => ['React', 'TypeScript', 'JavaScript', 'CSS', 'Git'],
                'ats_min_score' => 60,
                'is_featured' => true,
                'deadline' => now()->addDays(30)->toDateString(),
            ],
            [
                'title' => 'Software Engineer Intern',
                'slug' => 'software-engineer-intern',
                'type' => 'internship',
                'category' => 'technology',
                'location_type' => 'hybrid',
                'location' => 'San Francisco, CA',
                'salary_min' => '$35',
                'salary_max' => '$45 / hr',
                'description' => "Join our core platform team as a Software Engineer Intern. You'll work alongside senior engineers to build next-generation user interfaces for enterprise analytics.\n\nResponsibilities:\n- Develop React components\n- Write unit tests\n- Participate in code reviews\n\nRequirements:\n- Pursuing CS degree\n- JavaScript/TypeScript experience",
                'requirements' => 'Pursuing CS degree, JavaScript experience',
                'skills' => ['React', 'TypeScript', 'Internship'],
                'ats_keywords' => ['React', 'TypeScript', 'JavaScript', 'Internship'],
                'ats_min_score' => 50,
                'is_featured' => true,
                'deadline' => now()->addDays(45)->toDateString(),
            ],
            [
                'title' => 'Financial Analyst Intern',
                'slug' => 'financial-analyst-intern',
                'type' => 'internship',
                'category' => 'finance',
                'location_type' => 'onsite',
                'location' => 'New York, NY',
                'salary_min' => '$35',
                'salary_max' => '$45 / hr',
                'description' => 'Support the finance team with data analysis, reporting, and financial modeling for enterprise clients.',
                'requirements' => 'Excel and finance coursework',
                'skills' => ['Excel', 'Finance', 'Analytics'],
                'ats_keywords' => ['Excel', 'Finance', 'Analytics', 'Reporting'],
                'ats_min_score' => 50,
                'is_featured' => false,
                'deadline' => now()->addDays(20)->toDateString(),
            ],
            [
                'title' => 'UX/UI Designer',
                'slug' => 'ux-ui-designer',
                'type' => 'full-time',
                'category' => 'design',
                'location_type' => 'remote',
                'location' => 'Remote',
                'salary_min' => '$90k',
                'salary_max' => '$120k / yr',
                'description' => 'Design beautiful, intuitive interfaces for our enterprise analytics platform. Work closely with product and engineering teams.',
                'requirements' => 'Figma portfolio required',
                'skills' => ['Figma', 'UI/UX', 'Design Systems'],
                'ats_keywords' => ['Figma', 'UI/UX', 'Design Systems', 'Prototyping'],
                'ats_min_score' => 50,
                'is_featured' => true,
                'deadline' => now()->addDays(60)->toDateString(),
            ],
            [
                'title' => 'Data Science Intern',
                'slug' => 'data-science-intern',
                'type' => 'internship',
                'category' => 'technology',
                'location_type' => 'remote',
                'location' => 'Remote',
                'salary_min' => '$30',
                'salary_max' => '$40 / hr',
                'description' => 'Work with our data team on machine learning models and analytics pipelines.',
                'requirements' => 'Python and SQL fundamentals',
                'skills' => ['Python', 'SQL', 'ML'],
                'ats_keywords' => ['Python', 'SQL', 'Machine Learning', 'Pandas'],
                'ats_min_score' => 50,
                'is_featured' => false,
                'deadline' => now()->addDays(25)->toDateString(),
            ],
            [
                'title' => 'Marketing Coordinator',
                'slug' => 'marketing-coordinator',
                'type' => 'full-time',
                'category' => 'marketing',
                'location_type' => 'onsite',
                'location' => 'Austin, TX',
                'salary_min' => '$55k',
                'salary_max' => '$70k / yr',
                'description' => 'Coordinate marketing campaigns and brand initiatives across digital channels.',
                'requirements' => 'Marketing internship or coursework',
                'skills' => ['Marketing', 'Social Media', 'Content'],
                'ats_keywords' => ['Marketing', 'Social Media', 'Content', 'SEO'],
                'ats_min_score' => 40,
                'is_featured' => false,
                'deadline' => now()->addDays(40)->toDateString(),
            ],
        ];

        $jobModels = [];
        foreach ($jobs as $jobData) {
            $jobModels[] = JobPosting::create([
                ...$jobData,
                'user_id' => $recruiter->id,
                'company_id' => $company->id,
                'status' => JobStatus::Active,
                'moderation_status' => ModerationStatus::Approved,
                'published_at' => now()->subDays(rand(1, 14)),
            ]);
        }

        $ats = app(\App\Services\AtsCvChecker::class);

        foreach ([
            [$jobModels[1]->id, ApplicationStatus::Screening, 3],
            [$jobModels[4]->id, ApplicationStatus::Interview, 5],
            [$jobModels[2]->id, ApplicationStatus::Applied, 1],
        ] as [$jobId, $status, $daysAgo]) {
            $application = Application::create([
                'user_id' => $student->id,
                'job_posting_id' => $jobId,
                'status' => $status,
                'applied_at' => now()->subDays($daysAgo),
                'match_score' => 0,
            ]);
            $ats->applyToApplication($application, false);
        }

        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(GrowthFeaturesSeeder::class);

        $this->command?->info('Seeded: admin@recruitpro.com / recruiter@recruitpro.com / student@recruitpro.com (password: password)');
    }
}
