<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MockSISSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // ── Computer Science (CSC) ────────────────────────────────────────
            [
                'matric_no'  => 'CSC/2021/001',
                'full_name'  => 'Adebayo Oluwaseun Emmanuel',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student1.jpg',
            ],
            [
                'matric_no'  => 'CSC/2020/006',
                'full_name'  => 'Afolabi Segun Adeyemi',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student6.jpg',
            ],
            [
                'matric_no'  => 'CSC/2022/007',
                'full_name'  => 'Temitope Funmilayo Adeleke',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student7.jpg',
            ],
            [
                'matric_no'  => 'CSC/2023/008',
                'full_name'  => 'Babatunde Oluwafemi Ogundimu',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student8.jpg',
            ],
            [
                'matric_no'  => 'CSC/2023/040',
                'full_name'  => 'Ayomide Oluwadarasimi Adesanya',
                'department' => 'Computer Science',
                'photo_path' => 'photos/student40.jpg',
            ],

            // ── Software Engineering (SEN) ────────────────────────────────────
            [
                'matric_no'  => 'SEN/2021/002',
                'full_name'  => 'Chinwe Ifeoma Okonkwo',
                'department' => 'Software Engineering',
                'photo_path' => 'photos/student2.jpg',
            ],
            [
                'matric_no'  => 'SEN/2020/009',
                'full_name'  => 'Obinna Chukwuemeka Ibe',
                'department' => 'Software Engineering',
                'photo_path' => 'photos/student9.jpg',
            ],
            [
                'matric_no'  => 'SEN/2022/010',
                'full_name'  => 'Adaeze Perpetua Nwachukwu',
                'department' => 'Software Engineering',
                'photo_path' => 'photos/student10.jpg',
            ],

            // ── Information Technology (IFT) ──────────────────────────────────
            [
                'matric_no'  => 'IFT/2021/003',
                'full_name'  => 'Musa Abdullahi Garba',
                'department' => 'Information Technology',
                'photo_path' => 'photos/student3.jpg',
            ],
            [
                'matric_no'  => 'IFT/2020/011',
                'full_name'  => 'Aminu Suleiman Bello',
                'department' => 'Information Technology',
                'photo_path' => 'photos/student11.jpg',
            ],
            [
                'matric_no'  => 'IFT/2022/012',
                'full_name'  => 'Halima Yusuf Abdulkadir',
                'department' => 'Information Technology',
                'photo_path' => 'photos/student12.jpg',
            ],
            [
                'matric_no'  => 'IFT/2023/013',
                'full_name'  => 'Etim Bassey Okon',
                'department' => 'Information Technology',
                'photo_path' => 'photos/student13.jpg',
            ],

            // ── Cyber Security (CYS) ──────────────────────────────────────────
            [
                'matric_no'  => 'CYS/2021/004',
                'full_name'  => 'Ngozi Chinyere Eze',
                'department' => 'Cyber Security',
                'photo_path' => 'photos/student4.jpg',
            ],
            [
                'matric_no'  => 'CYS/2020/014',
                'full_name'  => 'Ugochukwu Chidiebere Obi',
                'department' => 'Cyber Security',
                'photo_path' => 'photos/student14.jpg',
            ],
            [
                'matric_no'  => 'CYS/2022/015',
                'full_name'  => 'Yetunde Abimbola Lawal',
                'department' => 'Cyber Security',
                'photo_path' => 'photos/student15.jpg',
            ],

            // ── Data Science (DTS) ────────────────────────────────────────────
            [
                'matric_no'  => 'DTS/2021/005',
                'full_name'  => 'Emeka Tochukwu Nwosu',
                'department' => 'Data Science',
                'photo_path' => 'photos/student5.jpg',
            ],
            [
                'matric_no'  => 'DTS/2020/016',
                'full_name'  => 'Fatima Binta Aliyu',
                'department' => 'Data Science',
                'photo_path' => 'photos/student16.jpg',
            ],
            [
                'matric_no'  => 'DTS/2022/017',
                'full_name'  => 'Olumide Babajide Akintola',
                'department' => 'Data Science',
                'photo_path' => 'photos/student17.jpg',
            ],

            // ── Electrical Engineering (EEE) ──────────────────────────────────
            [
                'matric_no'  => 'EEE/2021/018',
                'full_name'  => 'Ibrahim Yakubu Musa',
                'department' => 'Electrical Engineering',
                'photo_path' => 'photos/student18.jpg',
            ],
            [
                'matric_no'  => 'EEE/2021/019',
                'full_name'  => 'Chioma Blessing Okafor',
                'department' => 'Electrical Engineering',
                'photo_path' => 'photos/student19.jpg',
            ],
            [
                'matric_no'  => 'EEE/2022/020',
                'full_name'  => 'Oluwatobi Adebimpe Fasanya',
                'department' => 'Electrical Engineering',
                'photo_path' => 'photos/student20.jpg',
            ],
            [
                'matric_no'  => 'EEE/2023/021',
                'full_name'  => 'Sani Abubakar Danjuma',
                'department' => 'Electrical Engineering',
                'photo_path' => 'photos/student21.jpg',
            ],

            // ── Mechanical Engineering (MEE) ──────────────────────────────────
            [
                'matric_no'  => 'MEE/2021/022',
                'full_name'  => 'Osagie Emmanuel Iyamu',
                'department' => 'Mechanical Engineering',
                'photo_path' => 'photos/student22.jpg',
            ],
            [
                'matric_no'  => 'MEE/2022/023',
                'full_name'  => 'Adunola Victoria Oladele',
                'department' => 'Mechanical Engineering',
                'photo_path' => 'photos/student23.jpg',
            ],
            [
                'matric_no'  => 'MEE/2023/024',
                'full_name'  => 'Chukwuebuka Somto Nzekwe',
                'department' => 'Mechanical Engineering',
                'photo_path' => 'photos/student24.jpg',
            ],

            // ── Civil Engineering (CVE) ───────────────────────────────────────
            [
                'matric_no'  => 'CVE/2021/025',
                'full_name'  => 'Hauwa Garba Shehu',
                'department' => 'Civil Engineering',
                'photo_path' => 'photos/student25.jpg',
            ],
            [
                'matric_no'  => 'CVE/2022/026',
                'full_name'  => 'Oluwakemi Taiwo Badmus',
                'department' => 'Civil Engineering',
                'photo_path' => 'photos/student26.jpg',
            ],
            [
                'matric_no'  => 'CVE/2023/027',
                'full_name'  => 'Kelechi Ifeanyi Onyekwere',
                'department' => 'Civil Engineering',
                'photo_path' => 'photos/student27.jpg',
            ],

            // ── Chemical Engineering (CHE) ────────────────────────────────────
            [
                'matric_no'  => 'CHE/2021/028',
                'full_name'  => 'Usman Tukur Balarabe',
                'department' => 'Chemical Engineering',
                'photo_path' => 'photos/student28.jpg',
            ],
            [
                'matric_no'  => 'CHE/2022/029',
                'full_name'  => 'Chidinma Oluchi Umeh',
                'department' => 'Chemical Engineering',
                'photo_path' => 'photos/student29.jpg',
            ],

            // ── Mathematics (MTH) ─────────────────────────────────────────────
            [
                'matric_no'  => 'MTH/2021/030',
                'full_name'  => 'Sunday Efemwonki Oghenekaro',
                'department' => 'Mathematics',
                'photo_path' => 'photos/student30.jpg',
            ],
            [
                'matric_no'  => 'MTH/2022/031',
                'full_name'  => 'Aisha Mohammed Kabiru',
                'department' => 'Mathematics',
                'photo_path' => 'photos/student31.jpg',
            ],

            // ── Physics (PHY) ─────────────────────────────────────────────────
            [
                'matric_no'  => 'PHY/2021/032',
                'full_name'  => 'Ekanem Okon Etim',
                'department' => 'Physics',
                'photo_path' => 'photos/student32.jpg',
            ],
            [
                'matric_no'  => 'PHY/2022/033',
                'full_name'  => 'Oluwasegun Akinwale Adeniyi',
                'department' => 'Physics',
                'photo_path' => 'photos/student33.jpg',
            ],

            // ── Accounting (ACC) ──────────────────────────────────────────────
            [
                'matric_no'  => 'ACC/2021/034',
                'full_name'  => 'Nkechi Amaka Eze',
                'department' => 'Accounting',
                'photo_path' => 'photos/student34.jpg',
            ],
            [
                'matric_no'  => 'ACC/2022/035',
                'full_name'  => 'Abdulrahman Musa Tanko',
                'department' => 'Accounting',
                'photo_path' => 'photos/student35.jpg',
            ],

            // ── Business Administration (BUS) ─────────────────────────────────
            [
                'matric_no'  => 'BUS/2021/036',
                'full_name'  => 'Blessing Efeturi Ovwasa',
                'department' => 'Business Administration',
                'photo_path' => 'photos/student36.jpg',
            ],
            [
                'matric_no'  => 'BUS/2022/037',
                'full_name'  => 'Taiwo Adenike Olatunde',
                'department' => 'Business Administration',
                'photo_path' => 'photos/student37.jpg',
            ],

            // ── Economics (ECO) ───────────────────────────────────────────────
            [
                'matric_no'  => 'ECO/2021/038',
                'full_name'  => 'Chibuzor Onyedikachi Okoro',
                'department' => 'Economics',
                'photo_path' => 'photos/student38.jpg',
            ],
            [
                'matric_no'  => 'ECO/2022/039',
                'full_name'  => 'Zainab Idris Shuaib',
                'department' => 'Economics',
                'photo_path' => 'photos/student39.jpg',
            ],
        ];

        // upsert on the primary key — safe to run multiple times.
        // New records are inserted; existing matric_nos update their other fields.
        DB::table('mock_sis')->upsert(
            $students,
            ['matric_no'],
            ['full_name', 'department', 'photo_path'],
        );
    }
}
