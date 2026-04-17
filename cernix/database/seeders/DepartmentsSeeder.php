<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // Faculty of Computing
            ['dept_name' => 'Computer Science',         'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Software Engineering',     'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Information Technology',   'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Cyber Security',           'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Data Science',             'faculty' => 'Faculty of Computing'],

            // Faculty of Engineering
            ['dept_name' => 'Electrical Engineering',   'faculty' => 'Faculty of Engineering'],
            ['dept_name' => 'Mechanical Engineering',   'faculty' => 'Faculty of Engineering'],
            ['dept_name' => 'Civil Engineering',        'faculty' => 'Faculty of Engineering'],
            ['dept_name' => 'Chemical Engineering',     'faculty' => 'Faculty of Engineering'],

            // Faculty of Sciences
            ['dept_name' => 'Mathematics',              'faculty' => 'Faculty of Sciences'],
            ['dept_name' => 'Physics',                  'faculty' => 'Faculty of Sciences'],
            ['dept_name' => 'Chemistry',                'faculty' => 'Faculty of Sciences'],

            // Faculty of Management Sciences
            ['dept_name' => 'Accounting',               'faculty' => 'Faculty of Management Sciences'],
            ['dept_name' => 'Business Administration',  'faculty' => 'Faculty of Management Sciences'],
            ['dept_name' => 'Economics',                'faculty' => 'Faculty of Management Sciences'],
        ];

        DB::table('departments')->insertOrIgnore($departments);
    }
}
