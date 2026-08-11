<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Admin,
        ]);

        $recruiter = User::create([
            'name' => 'TechFlow HR',
            'email' => 'recruiter@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Recruiter,
        ]);

        $company = Company::create([
            'user_id' => $recruiter->id,
            'name' => 'TechFlow Systems',
            'slug' => 'techflow-systems',
            'website' => 'https://techflow.example.com',
            'description' => 'Enterprise analytics platform building next-generation tools.',
        ]);

        $student = User::create([
            'name' => 'Alex Johnson',
            'email' => 'student@recruitpro.com',
            'password' => 'password',
            'role' => UserRole::Student,
        ]);

        Profile::create([
            'user_id' => $student->id,
            'headline' => 'CS Student at State University',
            'bio' => 'Passionate computer science student with experience in web development.',
            'phone' => '+1 (555) 123-4567',
            'location' => 'San Francisco, CA',
            'skills' => ['JavaScript', 'React', 'Python', 'SQL', 'Git'],
            'education' => [
                'school' => 'State University',
                'degree' => 'B.S. Computer Science',
                'graduation_year' => 2026,
                'gpa' => '3.8',
            ],
            'completeness' => 72,
        ]);

        $jobs = [
            [
                'title' => 'Senior Frontend Engineer',
                'slug' => 'senior-frontend-engineer',
                'type' => 'full-time',
                'location_type' => 'hybrid',
                'location' => 'San Francisco, CA',
                'salary_min' => '$140k',
                'salary_max' => '$180k / yr',
                'description' => "Join our core platform team to build next-generation user interfaces for enterprise analytics. You'll lead architectural decisions and mentor junior developers in a fast-paced environment.\n\nResponsibilities:\n- Develop React components\n- Collaborate with design teams\n- Write unit tests\n\nRequirements:\n- 5+ years experience\n- React & TypeScript expertise",
                'skills' => ['React', 'TypeScript', 'Full-time'],
                'is_featured' => true,
            ],
            [
                'title' => 'Software Engineer Intern',
                'slug' => 'software-engineer-intern',
                'type' => 'internship',
                'location_type' => 'hybrid',
                'location' => 'San Francisco, CA',
                'salary_min' => '$35',
                'salary_max' => '$45 / hr',
                'description' => "Join our core platform team as a Software Engineer Intern. You'll work alongside senior engineers to build next-generation user interfaces for enterprise analytics.\n\nResponsibilities:\n- Develop React components\n- Write unit tests\n- Participate in code reviews\n\nRequirements:\n- Pursuing CS degree\n- JavaScript/TypeScript experience",
                'skills' => ['React', 'TypeScript', 'Internship'],
                'is_featured' => true,
            ],
            [
                'title' => 'Financial Analyst Intern',
                'slug' => 'financial-analyst-intern',
                'type' => 'internship',
                'location_type' => 'onsite',
                'location' => 'New York, NY',
                'salary_min' => '$35',
                'salary_max' => '$45 / hr',
                'description' => 'Support the finance team with data analysis, reporting, and financial modeling for enterprise clients.',
                'skills' => ['Excel', 'Finance', 'Analytics'],
                'is_featured' => false,
            ],
            [
                'title' => 'UX/UI Designer',
                'slug' => 'ux-ui-designer',
                'type' => 'full-time',
                'location_type' => 'remote',
                'location' => 'Remote',
                'salary_min' => '$90k',
                'salary_max' => '$120k / yr',
                'description' => 'Design beautiful, intuitive interfaces for our enterprise analytics platform. Work closely with product and engineering teams.',
                'skills' => ['Figma', 'UI/UX', 'Design Systems'],
                'is_featured' => true,
            ],
            [
                'title' => 'Data Science Intern',
                'slug' => 'data-science-intern',
                'type' => 'internship',
                'location_type' => 'remote',
                'location' => 'Remote',
                'salary_min' => '$30',
                'salary_max' => '$40 / hr',
                'description' => 'Work with our data team on machine learning models and analytics pipelines.',
                'skills' => ['Python', 'SQL', 'ML'],
                'is_featured' => false,
            ],
            [
                'title' => 'Marketing Coordinator',
                'slug' => 'marketing-coordinator',
                'type' => 'full-time',
                'location_type' => 'onsite',
                'location' => 'Austin, TX',
                'salary_min' => '$55k',
                'salary_max' => '$70k / yr',
                'description' => 'Coordinate marketing campaigns and brand initiatives across digital channels.',
                'skills' => ['Marketing', 'Social Media', 'Content'],
                'is_featured' => false,
            ],
        ];

        $jobModels = [];
        foreach ($jobs as $jobData) {
            $jobModels[] = JobPosting::create([
                ...$jobData,
                'user_id' => $recruiter->id,
                'company_id' => $company->id,
                'status' => JobStatus::Active,
                'published_at' => now()->subDays(rand(1, 14)),
            ]);
        }

        Application::create([
            'user_id' => $student->id,
            'job_posting_id' => $jobModels[1]->id,
            'status' => ApplicationStatus::Screening,
            'applied_at' => now()->subDays(3),
            'match_score' => 92,
        ]);

        Application::create([
            'user_id' => $student->id,
            'job_posting_id' => $jobModels[4]->id,
            'status' => ApplicationStatus::Interview,
            'applied_at' => now()->subDays(5),
            'match_score' => 87,
        ]);

        Application::create([
            'user_id' => $student->id,
            'job_posting_id' => $jobModels[2]->id,
            'status' => ApplicationStatus::Applied,
            'applied_at' => now()->subDays(1),
            'match_score' => 78,
        ]);

        $this->command?->info('Seeded: admin@recruitpro.com / recruiter@recruitpro.com / student@recruitpro.com (password: password)');
    }
}
