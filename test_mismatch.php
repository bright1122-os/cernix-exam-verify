<?php
require __DIR__ . '/cernix/app/Services/CryptoService.php';

use App\Services\CryptoService;

$crypto = new CryptoService();

// Simulate session keys from seeder
$aesKey = bin2hex(random_bytes(32));
$hmacSecret = bin2hex(random_bytes(32));

echo "=== Testing Payload Structure Mismatch ===\n";

// RegistrationService payload (lines 100-106 of RegistrationService.php)
$registrationPayload = [
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'session_id' => 1,
    'timestamp'  => (new DateTime())->format('c'),
    'photo_hash' => hash('sha256', 'photos/student1.jpg'),
];

echo "RegistrationService payload fields: " . implode(', ', array_keys($registrationPayload)) . "\n";

// QrTokenService payload (lines 60-67 of QrTokenService.php)
$tokenPayload = [
    'token_id'   => '550e8400-e29b-41d4-a716-446655440000',
    'matric_no'  => 'CSC/2021/001',
    'full_name'  => 'Adebayo Oluwaseun Emmanuel',
    'photo_path' => 'photos/student1.jpg',
    'session_id' => 1,
    'issued_at'  => (new DateTime())->format('c'),
];

echo "QrTokenService payload fields: " . implode(', ', array_keys($tokenPayload)) . "\n";

echo "\n--- Differences ---\n";
$regFields = array_keys($registrationPayload);
$tokenFields = array_keys($tokenPayload);

echo "Only in RegistrationService: " . implode(', ', array_diff($regFields, $tokenFields)) . "\n";
echo "Only in QrTokenService: " . implode(', ', array_diff($tokenFields, $regFields)) . "\n";
echo "Common fields: " . implode(', ', array_intersect($regFields, $tokenFields)) . "\n";

echo "\n--- VerificationService Expectations ---\n";
echo "VerificationService decrypts the payload and then:\n";
echo "1. Looks up student using: payload['matric_no']\n";
echo "2. Compares: payload['session_id'] with qrData['session_id']\n";
echo "3. Compares: student->matric_no with payload['matric_no']\n";
echo "4. Returns student data including: full_name, matric_no, department, photo_path\n";

echo "\n--- Both payloads contain required fields ---\n";
echo "matric_no: " . (isset($registrationPayload['matric_no']) ? 'YES' : 'NO') . " (Reg) / " . (isset($tokenPayload['matric_no']) ? 'YES' : 'NO') . " (Token)\n";
echo "session_id: " . (isset($registrationPayload['session_id']) ? 'YES' : 'NO') . " (Reg) / " . (isset($tokenPayload['session_id']) ? 'YES' : 'NO') . " (Token)\n";
echo "full_name: " . (isset($registrationPayload['full_name']) ? 'YES' : 'NO') . " (Reg) / " . (isset($tokenPayload['full_name']) ? 'YES' : 'NO') . " (Token)\n";
echo "photo_path: " . (isset($registrationPayload['photo_path']) ? 'YES' : 'NO') . " (Reg) / " . (isset($tokenPayload['photo_path']) ? 'YES' : 'NO') . " (Token)\n";
