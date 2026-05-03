<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'level')) {
                $table->string('level')->nullable()->after('department_id');
            }
        });

        Schema::table('mock_sis', function (Blueprint $table) {
            if (! Schema::hasColumn('mock_sis', 'level')) {
                $table->string('level')->nullable()->after('department');
            }
        });

        DB::table('students')->whereNull('level')->update(['level' => '300']);
        DB::table('mock_sis')->whereNull('level')->update(['level' => '300']);

        if (! Schema::hasTable('timetables')) {
            Schema::create('timetables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_session_id');
                $table->unsignedBigInteger('department_id');
                $table->string('level', 30);
                $table->string('course_code', 30);
                $table->string('course_title')->nullable();
                $table->date('exam_date');
                $table->time('start_time');
                $table->time('end_time')->nullable();
                $table->string('venue');
                $table->unsignedInteger('capacity')->nullable();
                $table->string('status', 20)->default('scheduled');
                $table->timestamps();

                $table->foreign('exam_session_id')
                    ->references('session_id')
                    ->on('exam_sessions')
                    ->cascadeOnDelete();

                $table->foreign('department_id')
                    ->references('dept_id')
                    ->on('departments')
                    ->cascadeOnDelete();

                $table->unique(
                    ['exam_session_id', 'department_id', 'level', 'course_code', 'exam_date', 'start_time'],
                    'timetables_unique_exam_slot'
                );

                $table->index(['exam_session_id', 'department_id', 'level', 'exam_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
