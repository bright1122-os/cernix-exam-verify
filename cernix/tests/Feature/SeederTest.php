<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_exam_sessions_has_exactly_one_active_session(): void
    {
        $activeCount = DB::table('exam_sessions')->where('is_active', true)->count();

        $this->assertSame(1, $activeCount);
    }

    public function test_mock_sis_has_at_least_thirty_records(): void
    {
        $count = DB::table('mock_sis')->count();

        $this->assertGreaterThanOrEqual(30, $count);
    }

    public function test_mock_sis_all_records_have_photo_path(): void
    {
        $missing = DB::table('mock_sis')
            ->where('photo_path', '')
            ->orWhereNull('photo_path')
            ->count();

        $this->assertSame(0, $missing, 'Every mock_sis record must have a non-empty photo_path');
    }

    public function test_mock_sis_spans_multiple_departments(): void
    {
        $deptCount = DB::table('mock_sis')->distinct()->count('department');

        $this->assertGreaterThanOrEqual(5, $deptCount, 'Students should span at least 5 departments');
    }

    public function test_departments_has_at_least_ten_records(): void
    {
        $count = DB::table('departments')->count();

        $this->assertGreaterThanOrEqual(10, $count);
    }

    public function test_mock_sis_departments_exist_in_departments_table(): void
    {
        $sisDepts  = DB::table('mock_sis')->distinct()->pluck('department');
        $knownDepts = DB::table('departments')->pluck('dept_name');

        foreach ($sisDepts as $dept) {
            $this->assertTrue(
                $knownDepts->contains($dept),
                "Department '{$dept}' in mock_sis has no matching row in departments table",
            );
        }
    }
}
