<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

// Simulate session keys from seeder
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

echo "=== Testing Student Lookup Scenario ===\n";

// Simulate RegistrationService payload
$registrationPayload = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => 1,
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

echo "RegistrationService payload: " . json_encode($registrationPayload) . "\n";

// Encrypt
$encrypted = $crypto->encryptPayload($registrationPayload, $aesKey, $hmacSecret);

// Simulate QR data
$qrData = [
    'token_id'          => '550e8400-e29b-41d4-a716-446655440000',
    'encrypted_payload' => $encrypted['encrypted_payload'],
    'hmac_signature'    => $encrypted['hmac_signature'],
    'session_id'        => 1,
];

// Simulate frontend JSON encoding/decoding
$jsonQr = json_encode($qrData);
$receivedQr = json_decode($jsonQr, true);

// Simulate VerificationService steps
echo "\n--- Verification Simulation ---\n";

// Step 5: Decrypt
try {
    $decryptedPayload = $crypto->decryptPayload(
        $receivedQr['encrypted_payload'],
        $receivedQr['hmac_signature'],
        $aesKey,
        $hmacSecret
    );
    echo "Step 5 (decrypt): PASS\n";
    echo "Decrypted payload: " . json_encode($decryptedPayload) . "\n";
} catch (RuntimeException $e) {
    die("FAIL: Decryption failed: " . $e->getMessage() . "\n");
}

// Step 6: Simulate student lookup (from VerificationService.php lines 106-110)
// The lookup uses: (string) ($payload['matric_no'] ?? '')
$matricNoLookup = (string) ($decryptedPayload['matric_no'] ?? '');
echo "Step 6: Student lookup for matric_no = '$matricNoLookup'\n";

// Simulate student found in database
$student = (object) [
    'matric_no' => 'CSC/2021/001',
    'full_name' => 'Adebayo Oluwaseun Emmanuel',
    'photo_path' => 'photos/student1.jpg',
    'department_name' => 'Computer Science',
];

echo "Student found: " . ($student ? 'YES' : 'NO') . "\n";
echo "Student matric_no: " . $student->matric_no . "\n";

// Step 7: Identity verification (from VerificationService.php lines 113-117)
$sessionMatch = isset($decryptedPayload['session_id'])
    && (int) $decryptedPayload['session_id'] === (int) $receivedQr['session_id'];

$matricMatch = $student
    && hash_equals((string) $student->matric_no, (string) ($decryptedPayload['matric_no'] ?? ''));

echo "Session match: " . ($sessionMatch ? 'PASS' : 'FAIL') . "\n";
echo "Matric match: " . ($matricMatch ? 'PASS' : 'FAIL') . "\n";

if (! $student || ! $sessionMatch || ! $matricMatch) {
    die("FAIL: Identity mismatch - would return REJECTED with reason 'identity_mismatch'\n");
}

echo "\n=== VERIFICATION WOULD SUCCEED ===\n";
