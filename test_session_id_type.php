<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

echo "=== Testing session_id Type Mismatch ===\n";

// Simulate session keys
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

// Test with integer session_id
$payloadInt = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => 1,  // integer
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

$encryptedInt = $crypto->encryptPayload($payloadInt, $aesKey, $hmacSecret);

// Test with string session_id
$payloadStr = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => '1',  // string
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

$encryptedStr = $crypto->encryptPayload($payloadStr, $aesKey, $hmacSecret);

echo "Payload with integer session_id: " . json_encode($payloadInt) . "\n";
echo "Payload with string session_id: " . json_encode($payloadStr) . "\n";

// Decrypt both
$decryptedInt = $crypto->decryptPayload($encryptedInt['encrypted_payload'], $encryptedInt['hmac_signature'], $aesKey, $hmacSecret);
$decryptedStr = $crypto->decryptPayload($encryptedStr['encrypted_payload'], $encryptedStr['hmac_signature'], $aesKey, $hmacSecret);

echo "Decrypted int session_id: " . $decryptedInt['session_id'] . " (type: " . gettype($decryptedInt['session_id']) . ")\n";
echo "Decrypted str session_id: " . $decryptedStr['session_id'] . " (type: " . gettype($decryptedStr['session_id']) . ")\n";

// Test VerificationService comparison logic (lines 113-114 of VerificationService.php)
$qrDataSessionId = 1;  // from QR envelope (always integer in buildQrCode)

$sessionMatchInt = isset($decryptedInt['session_id'])
    && (int) $decryptedInt['session_id'] === (int) $qrDataSessionId;

$sessionMatchStr = isset($decryptedStr['session_id'])
    && (int) $decryptedStr['session_id'] === (int) $qrDataSessionId;

echo "\nSession match (int payload): " . ($sessionMatchInt ? 'PASS' : 'FAIL') . "\n";
echo "Session match (str payload): " . ($sessionMatchStr ? 'PASS' : 'FAIL') . "\n";

// Test with JSON encoding/decoding (what actually happens)
$jsonInt = json_encode($payloadInt);
$decodedInt = json_decode($jsonInt, true);
echo "After JSON round-trip, session_id type: " . gettype($decodedInt['session_id']) . "\n";

$jsonStr = json_encode($payloadStr);
$decodedStr = json_decode($jsonStr, true);
echo "After JSON round-trip, session_id type: " . gettype($decodedStr['session_id']) . "\n";
