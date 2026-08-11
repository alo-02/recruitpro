<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('location_type');
            $table->string('location')->nullable();
            $table->string('salary_min')->nullable();
            $table->string('salary_max')->nullable();
            $table->text('description');
            $table->json('skills')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('type');
            $table->index('location_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
