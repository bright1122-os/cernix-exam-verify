<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('qr_tokens', 'qr_svg')) {
                $table->longText('qr_svg')->nullable()->after('hmac_signature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qr_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('qr_tokens', 'qr_svg')) {
                $table->dropColumn('qr_svg');
            }
        });
    }
};
