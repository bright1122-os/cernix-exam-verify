<?php

namespace Tests\Feature;

use App\Services\CryptoService;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class QrTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    private QrTokenService $service;
    private int $sessionId;
    private string $matricNo;
    private int $examinerId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new QrTokenService(new CryptoService());

        // Department
        $deptId = DB::table('departments')->insertGetId([
            'dept_name' => 'Computer Science',
            'faculty'   => 'Faculty of Computing',
        ]);

        // Active exam session with real keys
        $this->sessionId = DB::table('exam_sessions')->insertGetId([
            'semester'      => 'First Semester',
            'academic_year' => '2025/2026',
            'fee_amount'    => 10000.00,
            'aes_key'       => bin2hex(random_bytes(32)),
            'hmac_secret'   => bin2hex(random_bytes(32)),
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Student
        $this->matricNo = 'CSC/2021/001';
        DB::table('students')->insert([
            'matric_no'     => $this->matricNo,
            'full_name'     => 'Adebayo Oluwaseun Emmanuel',
            'department_id' => $deptId,
            'session_id'    => $this->sessionId,
            'photo_path'    => 'photos/student1.jpg',
            'created_at'    => now(),
        ]);

        // Examiner
        $this->examinerId = DB::table('examiners')->insertGetId([
            'full_name'     => 'Examiner One',
            'username'      => 'examiner1',
            'password_hash' => bcrypt('password123'),
            'role'          => 'examiner',
            'is_active'     => true,
            'created_at'    => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // issue()
    // -------------------------------------------------------------------------

    public function test_issue_returns_token_data_with_qr_image(): void
    {
        $result = $this->service->issue($this->matricNo, $this->sessionId);

        $this->assertArrayHasKey('token_id', $result);
        $this->assertArrayHasKey('qr_content', $result);
        $this->assertArrayHasKey('qr_svg', $result);
        $this->assertArrayHasKey('encrypted_payload', $result);
        $this->assertArrayHasKey('hmac_signature', $result);

        $this->assertTrue(Str::isUuid($result['token_id']));
        $this->assertNotEmpty($result['qr_svg']);

        // QR content must be valid JSON with required keys
        $qrData = json_decode($result['qr_content'], true);
        $this->assertSame(1, $qrData['v']);
        $this->assertSame($this->sessionId, $qrData['session_id']);
    }

    public function test_issue_stores_token_in_database(): void
    {
        $result = $this->service->issue($this->matricNo, $this->sessionId);

        $this->assertDatabaseHas('qr_tokens', [
            'token_id'   => $result['token_id'],
            'student_id' => $this->matricNo,
            'session_id' => $this->sessionId,
            'status'     => 'UNUSED',
        ]);
    }

    public function test_issue_throws_if_student_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not found/i');

        $this->service->issue('NONEXISTENT/000', $this->sessionId);
    }

    public function test_issue_throws_if_session_not_active(): void
    {
        DB::table('exam_sessions')->where('session_id', $this->sessionId)->update(['is_active' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not found/i');

        $this->service->issue($this->matricNo, $this->sessionId);
    }

    public function test_issue_throws_on_duplicate_unused_token(): void
    {
        $this->service->issue($this->matricNo, $this->sessionId);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/already has an active token/i');

        $this->service->issue($this->matricNo, $this->sessionId);
    }

    public function test_issue_allows_new_token_after_previous_is_used(): void
    {
        $first = $this->service->issue($this->matricNo, $this->sessionId);

        DB::table('qr_tokens')
            ->where('token_id', $first['token_id'])
            ->update(['status' => 'USED']);

        // Should not throw
        $second = $this->service->issue($this->matricNo, $this->sessionId);
        $this->assertNotSame($first['token_id'], $second['token_id']);
    }

    // -------------------------------------------------------------------------
    // verify()
    // -------------------------------------------------------------------------

    public function test_verify_approves_valid_unused_token(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);

        $result = $this->service->verify(
            $issued['qr_content'],
            $this->examinerId,
            'fp-abc123',
            '127.0.0.1'
        );

        $this->assertSame('APPROVED', $result['decision']);
        $this->assertSame($this->matricNo, $result['student']['matric_no']);
        $this->assertSame('Adebayo Oluwaseun Emmanuel', $result['student']['full_name']);
    }

    public function test_verify_marks_token_as_used_after_approval(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->verify($issued['qr_content'], $this->examinerId, 'fp', '127.0.0.1');

        $this->assertDatabaseHas('qr_tokens', [
            'token_id' => $issued['token_id'],
            'status'   => 'USED',
        ]);
    }

    public function test_verify_writes_verification_log(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->verify($issued['qr_content'], $this->examinerId, 'fp-device', '10.0.0.1');

        $this->assertDatabaseHas('verification_logs', [
            'token_id'    => $issued['token_id'],
            'examiner_id' => $this->examinerId,
            'decision'    => 'APPROVED',
            'device_fp'   => 'fp-device',
            'ip_address'  => '10.0.0.1',
        ]);
    }

    public function test_verify_returns_duplicate_for_already_used_token(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->verify($issued['qr_content'], $this->examinerId, 'fp', '127.0.0.1');

        // Second scan
        $result = $this->service->verify($issued['qr_content'], $this->examinerId, 'fp', '127.0.0.1');

        $this->assertSame('DUPLICATE', $result['decision']);
    }

    public function test_verify_returns_rejected_for_revoked_token(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->revoke($issued['token_id']);

        $result = $this->service->verify($issued['qr_content'], $this->examinerId, 'fp', '127.0.0.1');

        $this->assertSame('REJECTED', $result['decision']);
    }

    public function test_verify_throws_on_tampered_qr_content(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);

        $data = json_decode($issued['qr_content'], true);
        // Corrupt the encrypted payload
        $data['encrypted_payload'] = base64_encode('garbage_data_here');
        $tampered = json_encode($data);

        $this->expectException(RuntimeException::class);

        $this->service->verify($tampered, $this->examinerId, 'fp', '127.0.0.1');
    }

    public function test_verify_throws_on_invalid_qr_format(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/invalid qr code format/i');

        $this->service->verify('not-valid-json', $this->examinerId, 'fp', '127.0.0.1');
    }

    // -------------------------------------------------------------------------
    // revoke()
    // -------------------------------------------------------------------------

    public function test_revoke_marks_token_as_revoked(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->revoke($issued['token_id']);

        $this->assertDatabaseHas('qr_tokens', [
            'token_id' => $issued['token_id'],
            'status'   => 'REVOKED',
        ]);
    }

    public function test_revoke_throws_if_token_not_unused(): void
    {
        $issued = $this->service->issue($this->matricNo, $this->sessionId);
        $this->service->verify($issued['qr_content'], $this->examinerId, 'fp', '127.0.0.1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not in UNUSED state/i');

        $this->service->revoke($issued['token_id']);
    }

    public function test_revoke_throws_for_nonexistent_token(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->revoke(Str::uuid()->toString());
    }

    // -------------------------------------------------------------------------
    // buildQrCode()
    // -------------------------------------------------------------------------

    public function test_build_qr_code_returns_svg_string(): void
    {
        $svg = $this->service->buildQrCode('test-content');

        $this->assertNotEmpty($svg);
        // SVG documents start with the XML/SVG tag
        $this->assertStringContainsString('<svg', $svg);
    }
}
