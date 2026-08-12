<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('notification_preferences');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('plan', 30)->default('free')->after('rejection_reason');
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
            $table->unsignedTinyInteger('max_team_seats')->default(3)->after('plan_expires_at');
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->string('meeting_url')->nullable()->after('location_or_link');
            $table->string('calendar_uid')->nullable()->after('meeting_url');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('hiring_stage_id')->nullable()->after('status');
            $table->json('recruiter_tags')->nullable()->after('recruiter_notes');
        });

        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('repo_url')->nullable();
            $table->json('tech_stack')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('skill_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('skill')->index();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('pass_score')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_assessment_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->json('options');
            $table->unsignedTinyInteger('correct_index');
            $table->unsignedTinyInteger('points')->default(1);
            $table->timestamps();
        });

        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('company_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'company_id']);
        });

        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'company_id']);
        });

        Schema::create('company_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('seat_role')->default('recruiter'); // owner|recruiter|viewer
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('hiring_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('position')->default(0);
            $table->string('color', 20)->default('#64748b');
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('hiring_stage_id')->references('id')->on('hiring_stages')->nullOnDelete();
        });

        Schema::create('interview_scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('technical')->nullable();
            $table->unsignedTinyInteger('communication')->nullable();
            $table->unsignedTinyInteger('culture_fit')->nullable();
            $table->unsignedTinyInteger('overall')->nullable();
            $table->text('feedback')->nullable();
            $table->string('recommendation')->nullable(); // hire|maybe|no
            $table->timestamps();
            $table->unique(['interview_id', 'reviewer_id']);
        });

        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('salary')->nullable();
            $table->date('start_date')->nullable();
            $table->text('body');
            $table->string('status')->default('draft'); // draft|sent|accepted|declined|withdrawn
            $table->string('candidate_signature')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_blacklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('body');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('abuse_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('open'); // open|reviewing|resolved|dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('enabled')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint')->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('abuse_reports');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('candidate_blacklists');
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('interview_scorecards');
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['hiring_stage_id']);
            $table->dropColumn(['hiring_stage_id', 'recruiter_tags']);
        });
        Schema::dropIfExists('hiring_stages');
        Schema::dropIfExists('company_members');
        Schema::dropIfExists('company_reviews');
        Schema::dropIfExists('company_follows');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('skill_assessments');
        Schema::dropIfExists('portfolio_projects');
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn(['meeting_url', 'calendar_uid']);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_expires_at', 'max_team_seats']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
