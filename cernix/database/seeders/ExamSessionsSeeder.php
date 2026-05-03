<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('exam_sessions')
            ->where('semester', 'First Semester')
            ->where('academic_year', '2025/2026')
            ->exists();

        if (! $exists) {
            DB::table('exam_sessions')->insert([
                'semester'      => 'First Semester',
                'academic_year' => '2025/2026',
                'fee_amount'    => 100000.00,
                'aes_key'       => bin2hex(random_bytes(32)),
                'hmac_secret'   => bin2hex(random_bytes(32)),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
