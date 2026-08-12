<?php

namespace Database\Seeders;

use App\Models\AssessmentQuestion;
use App\Models\BlogPost;
use App\Models\FeatureFlag;
use App\Models\HiringStage;
use App\Models\SkillAssessment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GrowthFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'recommendations', 'label' => 'Job Recommendations', 'enabled' => true],
            ['key' => 'assessments', 'label' => 'Skill Assessments', 'enabled' => true],
            ['key' => 'offers', 'label' => 'Offer Letters', 'enabled' => true],
            ['key' => 'team_seats', 'label' => 'Company Team Seats', 'enabled' => true],
            ['key' => 'cms', 'label' => 'Career CMS', 'enabled' => true],
        ];
        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(['key' => $flag['key']], $flag);
        }

        $assessment = SkillAssessment::updateOrCreate(
            ['slug' => 'laravel-basics'],
            [
                'title' => 'Laravel Basics',
                'skill' => 'Laravel',
                'description' => 'Quick check of Laravel fundamentals for internship candidates.',
                'pass_score' => 60,
                'is_active' => true,
            ]
        );

        if ($assessment->questions()->count() === 0) {
            $questions = [
                ['What artisan command starts the server?', ['php artisan serve', 'php artisan start', 'composer serve', 'npm run serve'], 0],
                ['Which ORM does Laravel use?', ['Eloquent', 'Doctrine', 'Hibernate', 'Sequelize'], 0],
                ['Where do you define web routes by default?', ['routes/web.php', 'app/routes.php', 'config/routes.php', 'public/index.php'], 0],
            ];
            foreach ($questions as [$prompt, $options, $correct]) {
                AssessmentQuestion::create([
                    'skill_assessment_id' => $assessment->id,
                    'prompt' => $prompt,
                    'options' => $options,
                    'correct_index' => $correct,
                    'points' => 1,
                ]);
            }
        }

        $js = SkillAssessment::updateOrCreate(
            ['slug' => 'javascript-essentials'],
            [
                'title' => 'JavaScript Essentials',
                'skill' => 'JavaScript',
                'description' => 'Core JS knowledge for frontend roles.',
                'pass_score' => 60,
                'is_active' => true,
            ]
        );
        if ($js->questions()->count() === 0) {
            AssessmentQuestion::create([
                'skill_assessment_id' => $js->id,
                'prompt' => 'Which keyword declares a block-scoped variable?',
                'options' => ['var', 'let', 'function', 'static'],
                'correct_index' => 1,
                'points' => 1,
            ]);
            AssessmentQuestion::create([
                'skill_assessment_id' => $js->id,
                'prompt' => 'What does === check?',
                'options' => ['Value only', 'Type only', 'Value and type', 'Reference only'],
                'correct_index' => 2,
                'points' => 1,
            ]);
        }

        $admin = User::where('email', 'admin@recruitpro.com')->first();
        if ($admin) {
            BlogPost::updateOrCreate(
                ['slug' => 'how-to-write-an-internship-resume'],
                [
                    'author_id' => $admin->id,
                    'title' => 'How to Write an Internship Resume',
                    'excerpt' => 'Practical tips to highlight projects, skills, and coursework.',
                    'body' => "Focus on projects, measurable outcomes, and relevant coursework.\n\nKeep your resume to one page and tailor keywords to each role.\n\nUse RecruitPro's ATS keywords to improve your match score.",
                    'is_published' => true,
                    'published_at' => now()->subDay(),
                ]
            );
        }

        $recruiter = User::where('email', 'recruiter@recruitpro.com')->first();
        if ($recruiter?->company && $recruiter->company->hiringStages()->count() === 0) {
            foreach (['Applied', 'Phone Screen', 'Technical', 'Final', 'Offer'] as $i => $name) {
                HiringStage::create([
                    'company_id' => $recruiter->company->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'position' => $i,
                    'color' => ['#64748b', '#0ea5e9', '#8b5cf6', '#f59e0b', '#22c55e'][$i] ?? '#64748b',
                    'is_terminal' => $name === 'Offer',
                ]);
            }
            $recruiter->company->update(['plan' => 'pro', 'max_team_seats' => 10]);
        }
    }
}
