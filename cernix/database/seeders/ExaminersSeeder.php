<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExaminersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('examiners')->insert([
            [
                'full_name'     => 'Examiner One',
                'username'      => 'examiner1',
                'password_hash' => bcrypt('password123'),
                'role'          => 'examiner',
                'is_active'     => true,
                'created_at'    => now(),
            ],
            [
                'full_name'     => 'Admin One',
                'username'      => 'admin1',
                'password_hash' => bcrypt('admin123'),
                'role'          => 'admin',
                'is_active'     => true,
                'created_at'    => now(),
            ],
        ]);
    }
}
