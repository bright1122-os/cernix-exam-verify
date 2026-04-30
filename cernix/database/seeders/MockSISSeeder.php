<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MockSISSeeder extends Seeder
{
    public function run(): void
    {
        $seedStudents = [
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

        $students = $seedStudents;
        $prefixes = [
            'Computer Science'     => 'CSC',
            'Software Engineering' => 'SEN',
            'Information Technology' => 'IFT',
            'Cyber Security'       => 'CYS',
            'Data Science'         => 'DTS',
        ];
        $years = [2021, 2022, 2023, 2024, 2025];
        $firstNames = [
            'Amina', 'Tunde', 'Kehinde', 'Ifeanyi', 'Zainab', 'David', 'Mariam', 'Samuel', 'Chioma', 'Ridwan',
            'Temiloluwa', 'Esther', 'Ibrahim', 'Favour', 'Obinna', 'Hauwa', 'Ebuka', 'Precious', 'Ayomide', 'Grace',
        ];
        $middleNames = [
            'Adebisi', 'Chinedu', 'Olamide', 'Tolulope', 'Nkem', 'Abiola', 'Opeyemi', 'Ejiro', 'Folasade', 'Mubarak',
        ];
        $lastNames = [
            'Adeyemi', 'Okafor', 'Bello', 'Ogunleye', 'Nwachukwu', 'Balogun', 'Salami', 'Eze', 'Akinyemi', 'Mohammed',
            'Afolabi', 'Nwosu', 'Yakubu', 'Adebayo', 'Okon', 'Sanni', 'Ojo', 'Idris', 'Chukwu', 'Bakare',
        ];

        foreach ($prefixes as $department => $prefix) {
            foreach ($years as $year) {
                for ($i = 6; $i <= 65; $i++) {
                    $index = ($year + $i) % count($firstNames);
                    $students[] = [
                        'matric_no'  => sprintf('%s/%d/%03d', $prefix, $year, $i),
                        'full_name'  => $firstNames[$index] . ' ' . $middleNames[$i % count($middleNames)] . ' ' . $lastNames[($index + $i) % count($lastNames)],
                        'department' => $department,
                        'photo_path' => 'photos/student' . ((($i + $year) % 5) + 1) . '.jpg',
                    ];
                }
            }
        }

        foreach (array_chunk($students, 150) as $chunk) {
            DB::table('mock_sis')->insertOrIgnore($chunk);
        }
    }
}
