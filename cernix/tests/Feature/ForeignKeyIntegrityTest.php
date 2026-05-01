<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ForeignKeyIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('PRAGMA foreign_keys = ON');
        $this->seed();
    }

    public function test_deleting_student_cascades_tokens_logs_and_payments(): void
    {
        $deptId = (int) DB::table('departments')->value('dept_id');
        $sessionId = (int) DB::table('exam_sessions')->value('session_id');
        $examinerId = (int) DB::table('examiners')->where('username', 'examiner1')->value('examiner_id');
        $matricNo = 'FK/2026/001';
        $tokenId = '11111111-1111-4111-8111-111111111111';

        DB::table('students')->insert([
            'matric_no' => $matricNo,
            'full_name' => 'Foreign Key Student',
            'department_id' => $deptId,
            'session_id' => $sessionId,
            'photo_path' => 'photos/student1.jpg',
            'created_at' => now(),
        ]);

        DB::table('payment_records')->insert([
            'student_id' => $matricNo,
            'rrr_number' => 'RRR-FK-001',
            'amount_declared' => 10000,
            'amount_confirmed' => 10000,
            'remita_response' => json_encode(['status' => 'success']),
            'verified_at' => now(),
        ]);

        DB::table('qr_tokens')->insert([
            'token_id' => $tokenId,
            'student_id' => $matricNo,
            'session_id' => $sessionId,
            'encrypted_payload' => 'encrypted',
            'hmac_signature' => 'signature',
            'status' => 'USED',
            'issued_at' => now(),
            'used_at' => now(),
        ]);

        DB::table('verification_logs')->insert([
            'token_id' => $tokenId,
            'examiner_id' => $examinerId,
            'decision' => 'APPROVED',
            'timestamp' => now(),
            'device_fp' => 'test-device',
            'ip_address' => '127.0.0.1',
        ]);

        DB::table('students')->where('matric_no', $matricNo)->delete();

        $this->assertDatabaseMissing('students', ['matric_no' => $matricNo]);
        $this->assertDatabaseMissing('payment_records', ['student_id' => $matricNo]);
        $this->assertDatabaseMissing('qr_tokens', ['token_id' => $tokenId]);
        $this->assertDatabaseMissing('verification_logs', ['token_id' => $tokenId]);
    }
}
