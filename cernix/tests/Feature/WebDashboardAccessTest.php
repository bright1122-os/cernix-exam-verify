<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_dashboard_refreshes_database_role_and_allows_admin(): void
    {
        $admin = DB::table('examiners')->where('username', 'admin1')->first();

        $this->withSession([
            'examiner_id' => (int) $admin->examiner_id,
            'examiner_role' => 'EXAMINER',
        ])->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Admin Dashboard');
    }

    public function test_examiner_dashboard_rejects_admin_like_actor(): void
    {
        $admin = DB::table('examiners')->where('username', 'admin1')->first();

        $this->withSession([
            'examiner_id' => (int) $admin->examiner_id,
            'examiner_role' => 'ADMIN',
        ])->get('/examiner/dashboard')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_examiner_dashboard_allows_only_active_examiner(): void
    {
        $examiner = DB::table('examiners')->where('username', 'examiner1')->first();

        $this->withSession([
            'examiner_id' => (int) $examiner->examiner_id,
            'examiner_role' => 'ADMIN',
        ])->get('/examiner/dashboard')
            ->assertOk()
            ->assertSee('Ready to scan');
    }
}
