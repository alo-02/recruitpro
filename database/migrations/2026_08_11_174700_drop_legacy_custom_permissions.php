<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop legacy custom permission tables so Spatie can own them.
        if (Schema::hasTable('permission_user')) {
            Schema::drop('permission_user');
        }

        if (Schema::hasTable('permissions') && ! Schema::hasColumn('permissions', 'guard_name')) {
            Schema::drop('permissions');
        }
    }

    public function down(): void
    {
        //
    }
};
