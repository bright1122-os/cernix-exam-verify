<?php

namespace Tests\Feature;

use App\Models\Examiner;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $admin = Examiner::where('username', 'admin1')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('Exam Sessions')
            ->assertSee('Examiners')
            ->assertSee('Students')
            ->assertSee('Recent Activity');
    }

    public function test_examiner_dashboard_rejects_admin_like_actor(): void
    {
        $admin = Examiner::where('username', 'admin1')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/examiner/dashboard')
            ->assertRedirect(route('examiner.login'));
    }

    public function test_examiner_dashboard_allows_only_active_examiner(): void
    {
        $examiner = Examiner::where('username', 'examiner1')->firstOrFail();

        $this->actingAs($examiner, 'examiner')
            ->get('/examiner/dashboard')
            ->assertOk()
            ->assertSee('Ready to scan');
    }

    public function test_admin_can_create_examiner_from_web_dashboard(): void
    {
        $admin = Examiner::where('username', 'admin1')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->post('/admin/examiners', [
            'full_name' => 'Web Examiner',
            'username' => 'webexaminer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertDatabaseHas('examiners', [
            'username' => 'webexaminer',
            'role' => 'examiner',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_assigned_session_from_web_dashboard(): void
    {
        $admin = Examiner::where('username', 'admin1')->firstOrFail();
        $examiner = DB::table('examiners')->where('username', 'examiner1')->first();

        $this->actingAs($admin, 'admin')
            ->post('/admin/sessions', [
            'name' => 'Second Semester',
            'fee_amount' => 100000,
            'examiner_id' => (int) $examiner->examiner_id,
        ])->assertRedirect();

        $this->assertDatabaseHas('exam_sessions', [
            'name' => 'Second Semester',
            'semester' => 'Second Semester',
            'examiner_id' => (int) $examiner->examiner_id,
        ]);
    }

    public function test_web_auth_flows_are_separated_by_role(): void
    {
        $student = Student::create([
            'matric_no' => 'CSC/2026/999',
            'full_name' => 'Auth Student',
            'department_id' => 1,
            'session_id' => 1,
            'photo_path' => 'photos/placeholder.jpg',
            'password' => Hash::make('studentpass'),
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->get('/admin/login')->assertOk()->assertSee('Admin Login');
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->post('/admin/login', ['username' => 'examiner1', 'password' => 'password123'])->assertSessionHasErrors('username');
        $this->post('/admin/login', ['username' => $student->matric_no, 'password' => 'studentpass'])->assertSessionHasErrors('username');
        $this->post('/admin/login', ['username' => 'admin1', 'password' => 'admin123'])->assertRedirect(route('admin.dashboard'));

        $this->get('/examiner/login')->assertOk()->assertSee('Examiner Login');
        $this->get('/examiner/dashboard')->assertRedirect(route('examiner.login'));
        $this->post('/examiner/login', ['username' => 'admin1', 'password' => 'admin123'])->assertSessionHasErrors('username');
        $this->post('/examiner/login', ['username' => $student->matric_no, 'password' => 'studentpass'])->assertSessionHasErrors('username');
        $this->post('/examiner/login', ['username' => 'examiner1', 'password' => 'password123'])->assertRedirect(route('examiner.dashboard'));

        $this->get('/student/login')->assertOk()->assertSee('Student Login');
        $this->get('/student/dashboard')->assertRedirect(route('student.login'));
        $this->post('/student/login', ['student_id' => 'student@example.test', 'password' => 'studentpass'])->assertSessionHasErrors('student_id');
        $this->post('/student/login', ['student_id' => $student->matric_no, 'password' => 'studentpass'])->assertRedirect(route('student.dashboard'));
    }

    public function test_deactivated_accounts_cannot_login_to_web_portals(): void
    {
        DB::table('examiners')->where('username', 'examiner1')->update(['is_active' => false]);

        $student = Student::create([
            'matric_no' => 'CSC/2026/998',
            'full_name' => 'Inactive Student',
            'department_id' => 1,
            'session_id' => 1,
            'photo_path' => 'photos/placeholder.jpg',
            'password' => Hash::make('studentpass'),
            'is_active' => false,
            'created_at' => now(),
        ]);

        $this->post('/examiner/login', ['username' => 'examiner1', 'password' => 'password123'])
            ->assertSessionHasErrors('username');

        $this->post('/student/login', ['student_id' => $student->matric_no, 'password' => 'studentpass'])
            ->assertSessionHasErrors('student_id');
    }
}
