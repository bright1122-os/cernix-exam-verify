<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class RegistrationService
{
    public function __construct(
        private readonly MockSISService $sisService,
        private readonly RemitaService  $remitaService,
        private readonly CryptoService  $cryptoService,
    ) {}

    /**
     * Register a student for an exam session.
     *
     * Exact step order (per spec):
     *  1. Validate student in SIS
     *  2. Fetch active exam session
     *  3. Verify Remita payment
     *  4. Create student record (with duplicate guard)
     *  5. Build QR payload
     *  6. Encrypt payload (AES-256-GCM + HMAC)
     *  7. Store qr_tokens row
     *  8. Return structured response
     *
     * @param  array{matric_no: string, full_name: string, rrr_number: string, expected_amount: float, session_id: int} $data
     * @return array{success: bool, message: string, data: array}
     * @throws RuntimeException on any validation or verification failure
     */
    public function registerStudent(array $data): array
    {
        // ── Step 1: Validate student in SIS ──────────────────────────────────
        // Throws "Student not found in SIS" if absent.
        // full_name and photo_path are taken from SIS, never from user input.
        $sisStudent = $this->sisService->getStudentByMatric($data['matric_no']);

        // ── Step 2: Fetch active exam session ────────────────────────────────
        $session = DB::table('exam_sessions')
            ->where('session_id', $data['session_id'])
            ->where('is_active', true)
            ->first();

        if (! $session) {
            throw new RuntimeException('Invalid or inactive session');
        }

        // ── Step 3: Verify payment ───────────────────────────────────────────
        // Exceptions bubble up unchanged from RemitaService:
        //   "RRR has already been used for a payment record."
        //   "Payment verification failed: ..."
        //   "Payment amount mismatch: ..."
        $this->remitaService->verifyPayment($data['rrr_number'], (float) $data['expected_amount']);

        // ── Step 4: Create student record ────────────────────────────────────
        $dept = DB::table('departments')
            ->where('dept_name', $sisStudent['department'])
            ->first();

        if (! $dept) {
            throw new RuntimeException(
                "Department not found for SIS value: \"{$sisStudent['department']}\""
            );
        }

        $studentRow = [
            'full_name'     => $sisStudent['full_name'],   // SIS only — never user input
            'department_id' => $dept->dept_id,
            'session_id'    => $data['session_id'],
            'photo_path'    => $sisStudent['photo_path'],  // SIS only — never user input
            'created_at'    => now(),
        ];

        if (app()->environment('local')) {
            // Local test mode: allow re-registration of the same student so the
            // full registration→scan flow can be repeated without a db:seed reset.
            // updateOrInsert keeps the PK stable, preserving any existing FK rows.
            DB::table('students')->updateOrInsert(
                ['matric_no' => $data['matric_no']],
                $studentRow,
            );
        } else {
            if (DB::table('students')
                ->where('matric_no', $data['matric_no'])
                ->where('session_id', $data['session_id'])
                ->exists()
            ) {
                throw new RuntimeException('Student already registered for this session');
            }

            DB::table('students')->insert(
                array_merge(['matric_no' => $data['matric_no']], $studentRow)
            );
        }

        // ── Step 5: Prepare QR payload ───────────────────────────────────────
        $payload = [
            'matric_no'  => $data['matric_no'],
            'full_name'  => $sisStudent['full_name'],
            'session_id' => $data['session_id'],
            'timestamp'  => now()->toIso8601String(),
            'photo_hash' => hash('sha256', $sisStudent['photo_path']),
        ];

        // ── Step 6: Encrypt payload ──────────────────────────────────────────
        $encrypted = $this->cryptoService->encryptPayload(
            $payload,
            $session->aes_key,
            $session->hmac_secret
        );

        // ── Step 7: Store QR token ───────────────────────────────────────────
        $tokenId = Str::uuid()->toString();

        DB::table('qr_tokens')->insert([
            'token_id'          => $tokenId,
            'student_id'        => $data['matric_no'],
            'session_id'        => $data['session_id'],
            'encrypted_payload' => $encrypted['encrypted_payload'],
            'hmac_signature'    => $encrypted['hmac_signature'],
            'status'            => 'UNUSED',
            'issued_at'         => now(),
            'used_at'           => null,
        ]);

        // ── Step 8: Return response ──────────────────────────────────────────
        return [
            'success' => true,
            'message' => 'Registration successful',
            'data'    => [
                'matric_no'  => $data['matric_no'],
                'full_name'  => $sisStudent['full_name'],
                'token_id'   => $tokenId,
                'qr_payload' => $encrypted['encrypted_payload'],
                'photo_path' => $sisStudent['photo_path'],
            ],
        ];
    }
}
