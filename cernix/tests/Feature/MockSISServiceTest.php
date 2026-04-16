<?php

namespace Tests\Feature;

use App\Services\MockSISService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class MockSISServiceTest extends TestCase
{
    use RefreshDatabase;

    private MockSISService $sis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sis = new MockSISService();

        DB::table('mock_sis')->insert([
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
        ]);
    }

    // -------------------------------------------------------------------------
    // getStudentByMatric
    // -------------------------------------------------------------------------

    public function test_valid_matric_returns_correct_student_data(): void
    {
        $student = $this->sis->getStudentByMatric('CSC/2021/001');

        $this->assertSame('CSC/2021/001', $student['matric_no']);
        $this->assertSame('Adebayo Oluwaseun Emmanuel', $student['full_name']);
        $this->assertSame('Computer Science', $student['department']);
        $this->assertSame('photos/student1.jpg', $student['photo_path']);
    }

    public function test_returned_array_contains_exactly_the_four_required_keys(): void
    {
        $student = $this->sis->getStudentByMatric('SEN/2021/002');

        $this->assertSame(['matric_no', 'full_name', 'department', 'photo_path'], array_keys($student));
    }

    public function test_invalid_matric_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Student not found in SIS');

        $this->sis->getStudentByMatric('INVALID/0000/000');
    }

    public function test_empty_matric_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Student not found in SIS');

        $this->sis->getStudentByMatric('');
    }

    // -------------------------------------------------------------------------
    // getPhotoPath
    // -------------------------------------------------------------------------

    public function test_get_photo_path_returns_correct_path(): void
    {
        $path = $this->sis->getPhotoPath('CSC/2021/001');

        $this->assertSame('photos/student1.jpg', $path);
    }

    public function test_get_photo_path_returns_correct_path_for_second_student(): void
    {
        $path = $this->sis->getPhotoPath('SEN/2021/002');

        $this->assertSame('photos/student2.jpg', $path);
    }

    public function test_get_photo_path_throws_for_invalid_matric(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Student not found in SIS');

        $this->sis->getPhotoPath('NONEXISTENT/000');
    }
}
