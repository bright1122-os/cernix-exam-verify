<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_sessions', 'examiner_id')) {
                $table->unsignedBigInteger('examiner_id')->nullable()->index()->after('hmac_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_sessions', 'examiner_id')) {
                $table->dropColumn('examiner_id');
            }
        });
    }
};
