<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examiners', function (Blueprint $table) {
            $table->bigIncrements('examiner_id');
            $table->string('full_name');
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->enum('role', ['examiner', 'admin']);
            $table->boolean('is_active')->default(false);
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examiners');
    }
};
