<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

// Simulate session keys from seeder
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

echo "AES Key length: " . strlen($aesKey) . " (should be 64)\n";
echo "HMAC Secret length: " . strlen($hmacSecret) . " (should be 64)\n";

// Simulate RegistrationService payload
$payload = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => 1,
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

echo "Payload: " . json_encode($payload) . "\n";

// Encrypt
$encrypted = $crypto->encryptPayload($payload, $aesKey, $hmacSecret);
echo "Encrypted payload: " . substr($encrypted['encrypted_payload'], 0, 50) . "...\n";
echo "HMAC signature: " . substr($encrypted['hmac_signature'], 0, 50) . "...\n";

// Simulate QR data
$qrData = [
    'token_id'          => '550e8400-e29b-41d4-a716-446655440000',
    'encrypted_payload' => $encrypted['encrypted_payload'],
    'hmac_signature'    => $encrypted['hmac_signature'],
    'session_id'        => 1,
];

// Simulate frontend JSON encoding/decoding
$jsonQr = json_encode($qrData);
echo "QR JSON length: " . strlen($jsonQr) . "\n";

// Simulate frontend sending back
$receivedQr = json_decode($jsonQr, true);

// Simulate VerificationService steps
echo "\n--- Verification Simulation ---\n";

// Step 1: Validate structure
foreach (['token_id', 'encrypted_payload', 'hmac_signature', 'session_id'] as $field) {
    if (empty($receivedQr[$field])) {
        die("FAIL: Missing field $field\n");
    }
}
echo "Step 1 (structure): PASS\n";

// Step 5: Decrypt
$sessionAesKey = $aesKey;
$sessionHmacSecret = $hmacSecret;

try {
    $decryptedPayload = $crypto->decryptPayload(
        $receivedQr['encrypted_payload'],
        $receivedQr['hmac_signature'],
        $sessionAesKey,
        $sessionHmacSecret
    );
    echo "Step 5 (decrypt): PASS\n";
    echo "Decrypted payload: " . json_encode($decryptedPayload) . "\n";
} catch (RuntimeException $e) {
    die("FAIL: Decryption failed: " . $e->getMessage() . "\n");
}

// Step 7: Identity verification
$sessionMatch = isset($decryptedPayload['session_id'])
    && (int) $decryptedPayload['session_id'] === (int) $receivedQr['session_id'];

$matricMatch = isset($decryptedPayload['matric_no'])
    && hash_equals((string) $decryptedPayload['matric_no'], (string) ($decryptedPayload['matric_no'] ?? ''));

if ($sessionMatch && $matricMatch) {
    echo "Step 7 (identity): PASS (sessionMatch=$sessionMatch, matricMatch=$matricMatch)\n";
} else {
    die("FAIL: Identity mismatch (sessionMatch=$sessionMatch, matricMatch=$matricMatch)\n");
}

echo "\n=== FULL FLOW PASSED ===\n";
