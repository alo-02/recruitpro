<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('category', 50)->default('technology')->after('type')->index();
        });

        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 100)->index();
            $table->string('severity', 20)->default('warning')->index();
            $table->string('email')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });

        // Normalize legacy single education/experience objects into list form.
        $profiles = DB::table('profiles')->select('id', 'education', 'experience')->get();
        foreach ($profiles as $profile) {
            $education = json_decode($profile->education ?? 'null', true);
            $experience = json_decode($profile->experience ?? 'null', true);
            $changed = false;

            if (is_array($education) && $education !== [] && array_key_exists('school', $education)) {
                $education = [$education];
                $changed = true;
            }
            if (is_array($experience) && $experience !== [] && array_key_exists('title', $experience)) {
                $experience = [$experience];
                $changed = true;
            }

            if ($changed) {
                DB::table('profiles')->where('id', $profile->id)->update([
                    'education' => $education ? json_encode($education) : null,
                    'experience' => $experience ? json_encode($experience) : null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
