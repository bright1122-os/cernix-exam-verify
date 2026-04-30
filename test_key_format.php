<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

echo "=== Testing Key Format from Database ===\n";

// ExamSessionsSeeder stores keys as hex (lines 16-17)
$hexKey = bin2hex(random_bytes(32));
$hexSecret = bin2hex(random_bytes(32));

echo "Hex key length: " . strlen($hexKey) . " (should be 64)\n";
echo "Hex secret length: " . strlen($hexSecret) . " (should be 64)\n";

// Test 1: Encrypt with hex key
$payload = ['test' => 'data'];
$encrypted = $crypto->encryptPayload($payload, $hexKey, $hexSecret);
echo "Encrypt with hex key: PASS\n";

// Test 2: Decrypt with hex key
try {
    $decrypted = $crypto->decryptPayload($encrypted['encrypted_payload'], $encrypted['hmac_signature'], $hexKey, $hexSecret);
    echo "Decrypt with hex key: PASS\n";
    echo "Decrypted: " . json_encode($decrypted) . "\n";
} catch (RuntimeException $e) {
    die("FAIL: Decrypt with hex key failed: " . $e->getMessage() . "\n");
}

// Test 3: What if database stores raw binary keys instead?
$binaryKey = random_bytes(32);
$binarySecret = random_bytes(32);

echo "\nBinary key length: " . strlen($binaryKey) . " (should be 32)\n";
echo "Binary secret length: " . strlen($binarySecret) . " (should be 32)\n";

// Encrypt with binary key
$encrypted2 = $crypto->encryptPayload($payload, $binaryKey, $binarySecret);
echo "Encrypt with binary key: PASS\n";

// Decrypt with binary key
try {
    $decrypted2 = $crypto->decryptPayload($encrypted2['encrypted_payload'], $encrypted2['hmac_signature'], $binaryKey, $binarySecret);
    echo "Decrypt with binary key: PASS\n";
} catch (RuntimeException $e) {
    die("FAIL: Decrypt with binary key failed: " . $e->getMessage() . "\n");
}

// Test 4: Mismatch - encrypt with hex, decrypt with binary (simulating DB format mismatch)
echo "\n--- Testing Key Format Mismatch ---\n";
$encrypted3 = $crypto->encryptPayload($payload, $hexKey, $hexSecret);
try {
    $decrypted3 = $crypto->decryptPayload($encrypted3['encrypted_payload'], $encrypted3['hmac_signature'], $binaryKey, $binarySecret);
    echo "Decrypt with wrong key format: PASS (unexpected!)\n";
} catch (RuntimeException $e) {
    echo "Decrypt with wrong key format: FAIL (expected)\n";
    echo "Error: " . $e->getMessage() . "\n";
}
