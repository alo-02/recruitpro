<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->boolean('two_factor_enabled')->default(false)->after('is_blocked');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->json('notification_preferences')->nullable()->after('two_factor_confirmed_at');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['type', 'location_type', 'status']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'job_posting_id']);
        });

        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('keywords')->nullable();
            $table->string('location')->nullable();
            $table->string('job_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
        Schema::dropIfExists('saved_jobs');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['type', 'location_type', 'status']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_confirmed_at',
                'notification_preferences',
            ]);
        });
    }
};
