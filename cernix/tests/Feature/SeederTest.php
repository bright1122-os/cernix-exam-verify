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

    public function test_mock_sis_has_at_least_five_records(): void
    {
        $count = DB::table('mock_sis')->count();

        $this->assertGreaterThanOrEqual(5, $count);
    }
}
