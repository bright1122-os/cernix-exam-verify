<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimetablesSeeder extends Seeder
{
    public function run(): void
    {
        $session = DB::table('exam_sessions')->where('is_active', true)->orderByDesc('session_id')->first()
            ?: DB::table('exam_sessions')->orderByDesc('session_id')->first();

        if (! $session) {
            return;
        }

        $departments = DB::table('departments')->pluck('dept_id', 'dept_name');
        $entries = [
            ['Computer Science', '300', 'CSC301', 'Design and Analysis of Algorithms', today()->addDay()->toDateString(), '09:00', '12:00', 'Faculty Hall A'],
            ['Software Engineering', '300', 'SEN305', 'Software Quality Assurance', today()->addDay()->toDateString(), '13:00', '16:00', 'Faculty Hall B'],
            ['Information Technology', '300', 'IFT303', 'Network Administration', today()->toDateString(), '10:00', '12:30', 'ICT Centre'],
            ['Cyber Security', '300', 'CYS307', 'Applied Cryptography', today()->toDateString(), '14:00', '16:30', 'Security Lab'],
            ['Data Science', '300', 'DTS309', 'Statistical Machine Learning', today()->addDays(2)->toDateString(), '09:00', '12:00', 'Data Lab'],
        ];

        foreach ($entries as [$deptName, $level, $code, $title, $date, $start, $end, $venue]) {
            $departmentId = $departments[$deptName] ?? null;
            if (! $departmentId) {
                continue;
            }

            DB::table('timetables')->updateOrInsert(
                [
                    'exam_session_id' => $session->session_id,
                    'department_id' => $departmentId,
                    'level' => $level,
                    'course_code' => $code,
                    'exam_date' => $date,
                    'start_time' => $start,
                ],
                [
                    'course_title' => $title,
                    'end_time' => $end,
                    'venue' => $venue,
                    'capacity' => null,
                    'status' => 'scheduled',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
