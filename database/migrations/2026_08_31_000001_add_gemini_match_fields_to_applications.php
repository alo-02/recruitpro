<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('ai_summary')->nullable()->after('ats_missing_keywords');
            $table->string('ai_match_source', 32)->nullable()->after('ai_summary');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['ai_summary', 'ai_match_source']);
        });
    }
};
