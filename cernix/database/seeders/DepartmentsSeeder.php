<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['dept_name' => 'Computer Science',       'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Software Engineering',   'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Information Technology', 'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Cyber Security',         'faculty' => 'Faculty of Computing'],
            ['dept_name' => 'Data Science',           'faculty' => 'Faculty of Computing'],
        ];

        DB::table('departments')->insert($departments);
    }
}
