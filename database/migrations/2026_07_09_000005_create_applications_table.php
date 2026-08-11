<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('applied');
            $table->text('cover_letter')->nullable();
            $table->text('recruiter_notes')->nullable();
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->timestamp('applied_at');
            $table->timestamps();

            $table->unique(['user_id', 'job_posting_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
