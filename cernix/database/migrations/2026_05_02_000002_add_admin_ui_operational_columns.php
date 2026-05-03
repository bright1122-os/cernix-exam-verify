<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_sessions', 'name')) {
                $table->string('name')->nullable()->after('session_id');
            }
            if (! Schema::hasColumn('exam_sessions', 'scheduled_start')) {
                $table->timestamp('scheduled_start')->nullable()->after('examiner_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_sessions', 'scheduled_start')) {
                $table->dropColumn('scheduled_start');
            }
            if (Schema::hasColumn('exam_sessions', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
