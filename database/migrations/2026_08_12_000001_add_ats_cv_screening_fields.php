<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->json('ats_keywords')->nullable()->after('skills');
            $table->unsignedTinyInteger('ats_min_score')->default(50)->after('ats_keywords');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->longText('resume_text')->nullable()->after('resume_path');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->json('ats_matched_keywords')->nullable()->after('match_score');
            $table->json('ats_missing_keywords')->nullable()->after('ats_matched_keywords');
            $table->timestamp('ats_checked_at')->nullable()->after('ats_missing_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['ats_matched_keywords', 'ats_missing_keywords', 'ats_checked_at']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('resume_text');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['ats_keywords', 'ats_min_score']);
        });
    }
};
