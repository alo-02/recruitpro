<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('top_n')->default(10);
            $table->unsignedInteger('candidates_evaluated')->default(0);
            $table->json('job_requirements')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['job_posting_id', 'created_at']);
        });

        Schema::create('screening_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->boolean('ai_recommended')->default(false);
            $table->json('extracted_profile')->nullable();
            $table->json('matched_points')->nullable();
            $table->json('partial_matches')->nullable();
            $table->json('missing_points')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->text('ai_recommendation')->nullable();
            $table->timestamps();

            $table->unique(['screening_session_id', 'application_id']);
            $table->index(['screening_session_id', 'rank']);
        });

        Schema::create('screening_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['screening_session_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_decisions');
        Schema::dropIfExists('screening_results');
        Schema::dropIfExists('screening_sessions');
    }
};
