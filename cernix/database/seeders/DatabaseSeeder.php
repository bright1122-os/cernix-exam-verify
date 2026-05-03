<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentsSeeder::class,
            ExamSessionsSeeder::class,
            MockSISSeeder::class,
            ExaminersSeeder::class,
            TimetablesSeeder::class,
        ]);
    }
}
