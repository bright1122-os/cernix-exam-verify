<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MockSISSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'matric_no'  => 'CSC/2021/001',
                'full_name'  => 'Adebayo Oluwaseun Emmanuel',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student1.jpg',
            ],
            [
                'matric_no'  => 'SEN/2021/002',
                'full_name'  => 'Chinwe Ifeoma Okonkwo',
                'department' => 'Software Engineering',
                'photo_path' => 'photos/student2.jpg',
            ],
            [
                'matric_no'  => 'IFT/2021/003',
                'full_name'  => 'Musa Abdullahi Garba',
                'department' => 'Information Technology',
                'photo_path' => 'photos/student3.jpg',
            ],
            [
                'matric_no'  => 'CYS/2021/004',
                'full_name'  => 'Ngozi Chinyere Eze',
                'department' => 'Cyber Security',
                'photo_path' => 'photos/student4.jpg',
            ],
            [
                'matric_no'  => 'DTS/2021/005',
                'full_name'  => 'Emeka Tochukwu Nwosu',
                'department' => 'Data Science',
                'photo_path' => 'photos/student5.jpg',
            ],
        ];

        DB::table('mock_sis')->insertOrIgnore($students);
    }
}
