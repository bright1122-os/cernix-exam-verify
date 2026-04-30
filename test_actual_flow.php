<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

// Simulate session keys from seeder
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

echo "=== Testing ACTUAL Flow (RegistrationService -> QrTokenService::buildQrCode) ===\n";

// Step 1: RegistrationService creates the token (lines 100-106 of RegistrationService.php)
$registrationPayload = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => 1,
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

echo "1. RegistrationService payload: " . json_encode($registrationPayload) . "\n";

// Step 2: RegistrationService encrypts and stores in qr_tokens (lines 109-127)
$encrypted = $crypto->encryptPayload($registrationPayload, $aesKey, $hmacSecret);
$tokenId = '550e8400-e29b-41d4-a716-446655440000';

echo "2. Token stored in qr_tokens with encrypted_payload\n";

// Step 3: StudentWebController calls QrTokenService::buildQrCode (lines 84-89)
// It fetches the tokenRow from qr_tokens and passes it to buildQrCode
$tokenRow = (object) [
    'token_id' => $tokenId,
    'encrypted_payload' => $encrypted['encrypted_payload'],
    'hmac_signature' => $encrypted['hmac_signature'],
];

// buildQrCode receives: token_id, encrypted_payload, hmac_signature, session_id
$qrTokenData = [
    'token_id'          => $tokenRow->token_id,
    'encrypted_payload' => $tokenRow->encrypted_payload,
    'hmac_signature'    => $tokenRow->hmac_signature,
    'session_id'        => 1,
];

echo "3. QrTokenService::buildQrCode receives: " . json_encode(array_keys($qrTokenData)) . "\n";

// Step 4: buildQrCode JSON-encodes this (lines 219-224 of QrTokenService.php)
$qrContent = json_encode($qrTokenData);
echo "4. QR content JSON length: " . strlen($qrContent) . "\n";

// Step 5: Frontend scans and sends to backend
$receivedQr = json_decode($qrContent, true);

// Step 6: VerificationService::verifyQr receives qrData array
echo "\n--- VerificationService::verifyQr ---\n";

// Step 5 of VerificationService: Decrypt
try {
    $decryptedPayload = $crypto->decryptPayload(
        $receivedQr['encrypted_payload'],
        $receivedQr['hmac_signature'],
        $aesKey,
        $hmacSecret
    );
    echo "5. Decrypt: PASS\n";
    echo "   Decrypted payload: " . json_encode($decryptedPayload) . "\n";
} catch (RuntimeException $e) {
    die("FAIL: Decryption failed: " . $e->getMessage() . "\n");
}

// Step 6: Student lookup (lines 106-110 of VerificationService.php)
$matricNoLookup = (string) ($decryptedPayload['matric_no'] ?? '');
echo "6. Student lookup for matric_no = '$matricNoLookup'\n";

// Simulate student found in database (created by RegistrationService)
$student = (object) [
    'matric_no' => 'CSC/2021/001',
    'full_name' => 'Adebayo Oluwaseun Emmanuel',
    'photo_path' => 'photos/student1.jpg',
    'department_name' => 'Computer Science',
];

echo "   Student found: " . ($student ? 'YES' : 'NO') . "\n";

// Step 7: Identity verification (lines 113-117)
$sessionMatch = isset($decryptedPayload['session_id'])
    && (int) $decryptedPayload['session_id'] === (int) $receivedQr['session_id'];

$matricMatch = $student
    && hash_equals((string) $student->matric_no, (string) ($decryptedPayload['matric_no'] ?? ''));

echo "7. Session match: " . ($sessionMatch ? 'PASS' : 'FAIL') . "\n";
echo "   Matric match: " . ($matricMatch ? 'PASS' : 'FAIL') . "\n";

if (! $student || ! $sessionMatch || ! $matricMatch) {
    die("FAIL: Identity mismatch - would return REJECTED with reason 'identity_mismatch'\n");
}

echo "\n=== ACTUAL FLOW WOULD SUCCEED ===\n";
