<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class VerificationService
{
    public function __construct(private readonly CryptoService $crypto) {}

    /**
     * Verify a decoded QR payload and return an entry decision.
     *
     * Returns one of three statuses — never throws:
     *   APPROVED  — token is authentic, UNUSED, student identity confirmed
     *   DUPLICATE — token was already used (replay or concurrent scan)
     *   REJECTED  — any structural, cryptographic, or identity failure
     *
     * Exact step order (per spec):
     *  1.  Validate QR structure
     *  2.  Fetch qr_tokens row
     *  3.  Check token status (USED → DUPLICATE, REVOKED → REJECTED)
     *  4.  Fetch and validate exam session
     *  5.  Decrypt + HMAC-verify payload via CryptoService
     *  6.  Fetch student record from DB
     *  7.  Verify identity (session_id match, matric_no constant-time match)
     *  8.  Atomic UNUSED → USED transition (DB transaction + lockForUpdate)
     *  9.  Write verification_logs entry
     *  10. Return structured response
     *
     * @param  array  $qrData    Decoded JSON from the physical QR scan
     * @param  int    $examinerId
     * @param  string $deviceFp  Device fingerprint
     * @param  string $ip
     * @return array{status: string, student: array|null, token_id: string|null, timestamp: string}
     */
    public function verifyQr(array $qrData, int $examinerId, string $deviceFp, string $ip): array
    {
        $now       = now();
        $timestamp = $now->toIso8601String();

        // ── Step 1: Validate QR structure ────────────────────────────────────
        foreach (['token_id', 'encrypted_payload', 'hmac_signature', 'session_id'] as $field) {
            if (empty($qrData[$field])) {
                // No valid token FK to log against — return silently
                return $this->response('REJECTED', null, null, $timestamp);
            }
        }

        $tokenId = (string) $qrData['token_id'];

        // ── Step 2: Fetch token record ────────────────────────────────────────
        $token = DB::table('qr_tokens')->where('token_id', $tokenId)->first();

        if (! $token) {
            // No DB row to anchor a log entry — return silently
            return $this->response('REJECTED', null, $tokenId, $timestamp);
        }

        // ── Step 3: Check token status ────────────────────────────────────────
        if ($token->status === 'USED') {
            $this->log($tokenId, $examinerId, 'DUPLICATE', $deviceFp, $ip, $now);
            return $this->response('DUPLICATE', null, $tokenId, $timestamp);
        }

        if ($token->status === 'REVOKED') {
            $this->log($tokenId, $examinerId, 'REJECTED', $deviceFp, $ip, $now);
            return $this->response('REJECTED', null, $tokenId, $timestamp);
        }

        // ── Step 4: Fetch active exam session ─────────────────────────────────
        $session = DB::table('exam_sessions')
            ->where('session_id', (int) $qrData['session_id'])
            ->where('is_active', true)
            ->first();

        if (! $session) {
            $this->log($tokenId, $examinerId, 'REJECTED', $deviceFp, $ip, $now);
            return $this->response('REJECTED', null, $tokenId, $timestamp);
        }

        // ── Step 5: Decrypt and HMAC-verify payload ───────────────────────────
        // CryptoService checks HMAC first (constant-time), then decrypts with GCM.
        // Any tamper or key mismatch throws — we catch it here and reject cleanly.
        try {
            $payload = $this->crypto->decryptPayload(
                $qrData['encrypted_payload'],
                $qrData['hmac_signature'],
                $session->aes_key,
                $session->hmac_secret
            );
        } catch (RuntimeException) {
            $this->log($tokenId, $examinerId, 'REJECTED', $deviceFp, $ip, $now);
            return $this->response('REJECTED', null, $tokenId, $timestamp);
        }

        // ── Step 6: Fetch student record ──────────────────────────────────────
        $student = DB::table('students')
            ->where('matric_no', (string) ($payload['matric_no'] ?? ''))
            ->first();

        // ── Step 7: Identity verification ────────────────────────────────────
        // Three checks in a single gate:
        //   a) student row must exist
        //   b) session_id in payload must match the outer QR session_id
        //   c) decrypted matric_no must equal the DB matric_no (constant-time compare)
        $sessionMatch = isset($payload['session_id'])
            && (int) $payload['session_id'] === (int) $qrData['session_id'];

        $matricMatch = $student
            && hash_equals((string) $student->matric_no, (string) ($payload['matric_no'] ?? ''));

        if (! $student || ! $sessionMatch || ! $matricMatch) {
            $this->log($tokenId, $examinerId, 'REJECTED', $deviceFp, $ip, $now);
            return $this->response('REJECTED', null, $tokenId, $timestamp);
        }

        // ── Step 8: Atomic UNUSED → USED (DB transaction + row lock) ──────────
        // lockForUpdate() prevents a concurrent scan from approving twice.
        // We re-read status inside the transaction to close the race window.
        $decision = DB::transaction(function () use ($tokenId, $now): string {
            $locked = DB::table('qr_tokens')
                ->where('token_id', $tokenId)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status !== 'UNUSED') {
                return 'DUPLICATE';
            }

            DB::table('qr_tokens')
                ->where('token_id', $tokenId)
                ->update(['status' => 'USED', 'used_at' => $now]);

            return 'APPROVED';
        });

        if ($decision === 'DUPLICATE') {
            $this->log($tokenId, $examinerId, 'DUPLICATE', $deviceFp, $ip, $now);
            return $this->response('DUPLICATE', null, $tokenId, $timestamp);
        }

        // ── Step 9: Write verification log (APPROVED) ─────────────────────────
        $this->log($tokenId, $examinerId, 'APPROVED', $deviceFp, $ip, $now);

        // ── Step 10: Return structured response ───────────────────────────────
        return $this->response('APPROVED', [
            'full_name'     => $student->full_name,
            'matric_no'     => $student->matric_no,
            'department_id' => $student->department_id,
            'photo_path'    => $student->photo_path,
        ], $tokenId, $timestamp);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function response(string $status, ?array $student, ?string $tokenId, string $timestamp): array
    {
        return [
            'status'    => $status,
            'student'   => $student,
            'token_id'  => $tokenId,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Append an entry to verification_logs.
     * Only called when both token_id and examiner_id FKs are confirmed valid.
     */
    private function log(
        string $tokenId,
        int    $examinerId,
        string $decision,
        string $deviceFp,
        string $ip,
        mixed  $now
    ): void {
        DB::table('verification_logs')->insert([
            'token_id'    => $tokenId,
            'examiner_id' => $examinerId,
            'decision'    => $decision,
            'timestamp'   => $now,
            'device_fp'   => $deviceFp,
            'ip_address'  => $ip,
        ]);
    }
}
