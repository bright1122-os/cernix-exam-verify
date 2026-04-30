<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

// Simulate session keys from seeder
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

echo "=== Testing RegistrationService Payload Structure ===\n";

// Simulate RegistrationService payload (lines 100-106 of RegistrationService.php)
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

// Step 7: Identity verification (from VerificationService.php lines 113-117)
$sessionMatch = isset($decryptedPayload['session_id'])
    && (int) $decryptedPayload['session_id'] === (int) $receivedQr['session_id'];

$matricMatch = isset($decryptedPayload['matric_no'])
    && hash_equals((string) $decryptedPayload['matric_no'], (string) ($decryptedPayload['matric_no'] ?? ''));

echo "Session match: " . ($sessionMatch ? 'PASS' : 'FAIL') . "\n";
echo "Matric match: " . ($matricMatch ? 'PASS' : 'FAIL') . "\n";

// Check what fields are in the decrypted payload
echo "\nPayload fields: " . implode(', ', array_keys($decryptedPayload)) . "\n";

// Check if VerificationService expects fields that aren't there
echo "\nExpected fields by VerificationService:\n";
echo "- matric_no: " . (isset($decryptedPayload['matric_no']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- session_id: " . (isset($decryptedPayload['session_id']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- full_name: " . (isset($decryptedPayload['full_name']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- photo_path: " . (isset($decryptedPayload['photo_path']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- token_id: " . (isset($decryptedPayload['token_id']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- issued_at: " . (isset($decryptedPayload['issued_at']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- timestamp: " . (isset($decryptedPayload['timestamp']) ? 'PRESENT' : 'MISSING') . "\n";
echo "- photo_hash: " . (isset($decryptedPayload['photo_hash']) ? 'PRESENT' : 'MISSING') . "\n";
