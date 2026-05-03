<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'password')) {
                $table->string('password')->nullable()->after('photo_path');
            }

            if (! Schema::hasColumn('students', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }

            if (! Schema::hasColumn('students', 'remember_token')) {
                $table->rememberToken()->after('is_active');
            }
        });

        DB::table('students')
            ->whereNull('password')
            ->update(['password' => bcrypt('student123')]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('students', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('students', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
