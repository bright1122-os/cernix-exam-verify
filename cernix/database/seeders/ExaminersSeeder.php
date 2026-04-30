<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExaminersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('examiners')->insertOrIgnore([
            [
                'full_name'     => 'Examiner One',
                'username'      => 'examiner1',
                'password_hash' => bcrypt('password123'),
                'role'          => 'EXAMINER',
                'admin_user_id' => null,
                'is_active'     => true,
                'last_active_at'=> null,
                'created_at'    => now(),
            ],
            [
                'full_name'     => 'Admin One',
                'username'      => 'admin1',
                'password_hash' => bcrypt('admin123'),
                'role'          => 'ADMIN',
                'admin_user_id' => null,
                'is_active'     => true,
                'last_active_at'=> null,
                'created_at'    => now(),
            ],
        ]);
    }
}
