# Full Execution Trace - QR Verification Failure

## Summary
System-generated QR codes are being rejected by the backend. This document traces the complete QR lifecycle to identify the root cause.

## QR Generation Flow

### Method 1: StudentWebController::register() (Web)
**File**: `cernix/app/Http/Controllers/Web/StudentWebController.php`

1. **Lines 40-57**: Calls `RegistrationService::registerStudent()`
   - Creates student record in `students` table
   - Creates token record in `qr_tokens` table with payload from RegistrationService
   - RegistrationService payload (lines 100-106):
     ```php
     $payload = [
         'matric_no'  => $data['matric_no'],
         'full_name'  => $sisStudent['full_name'],
         'session_id' => $data['session_id'],
         'timestamp'  => now()->toIso8601String(),
         'photo_hash' => hash('sha256', $sisStudent['photo_path']),
     ];
     ```
   - This payload is encrypted and stored in `qr_tokens.encrypted_payload`

2. **Lines 79-89**: Calls `QrTokenService::buildQrCode()`
   - Fetches token from `qr_tokens` table
   - Passes to buildQrCode:
     ```php
     [
         'token_id'          => $result['data']['token_id'],
         'encrypted_payload' => $tokenRow->encrypted_payload,
         'hmac_signature'    => $tokenRow->hmac_signature,
         'session_id'        => (int) $session->session_id,
     ]
     ```
   - buildQrCode JSON-encodes this and generates QR

### Method 2: ExamController::registerExam() (API)
**File**: `cernix/app/Http/Controllers/Student/ExamController.php`

Same flow as Method 1 - uses RegistrationService then QrTokenService::buildQrCode()

### Method 3: QrTokenService::issue() (Direct)
**File**: `cernix/app/Services/QrTokenService.php`

This method is NOT used by the registration flow. It creates a different payload:
```php
$payload = [
    'token_id'   => $tokenId,
    'matric_no'  => $student->matric_no,
    'full_name'  => $student->full_name,
    'photo_path' => $student->photo_path,
    'session_id' => $sessionId,
    'issued_at'  => $issuedAt->toISOString(),
];
```

## Verification Flow

### Frontend (dashboard.blade.php)
**File**: `cernix/resources/views/examiner/dashboard.blade.php`

1. **Lines 2120-2151**: `handleQRCode()` function
   - Parses raw QR data as JSON
   - Validates it has `token_id` field
   - Sends to backend via POST to `/examiner/verify`
   - Request body: `{ qr_data: qrData }`

### Backend Entry Point
**File**: `cernix/app/Http/Controllers/Web/ExaminerWebController.php`

1. **Lines 104-148**: `verify()` method
   - Validates `qr_data` is an array
   - Calls `VerificationService::verifyQr($data['qr_data'], ...)`

### VerificationService::verifyQr()
**File**: `cernix/app/Services/VerificationService.php`

1. **Step 1 (lines 35-44)**: Validate QR structure
   - Checks for `token_id`, `session_id`, `encrypted_payload`, `hmac_signature`

2. **Step 2 (lines 46-50)**: Fetch token record
   - Looks up token in `qr_tokens` table by `token_id`

3. **Step 3 (lines 52-58)**: Check token status
   - Returns DUPLICATE if USED, REJECTED if REVOKED

4. **Step 4 (lines 60-68)**: Fetch active exam session
   - Looks up session in `exam_sessions` table
   - Gets `aes_key` and `hmac_secret` from session

5. **Step 5 (lines 70-79)**: Decrypt and HMAC-verify
   - Calls `CryptoService::decryptPayload()`
   - On failure: returns REJECTED with reason 'tampered_token'

6. **Step 6 (lines 105-110)**: Fetch student record
   - Looks up student in `students` table using `payload['matric_no']`

7. **Step 7 (lines 113-122)**: Identity verification
   - Compares `payload['session_id']` with `qrData['session_id']`
   - Compares `student->matric_no` with `payload['matric_no']`
   - On mismatch: returns REJECTED with reason 'identity_mismatch'

## Key Findings

### Payload Structure Mismatch
- **RegistrationService** stores payload with: `matric_no`, `full_name`, `session_id`, `timestamp`, `photo_hash`
- **QrTokenService::issue()** would store payload with: `token_id`, `matric_no`, `full_name`, `photo_path`, `session_id`, `issued_at`
- **VerificationService** expects: `matric_no`, `session_id` (for identity match)
- Both payloads contain the required fields for verification

### Key Format
- **ExamSessionsSeeder** stores keys as hex (64 characters) via `bin2hex(random_bytes(32))`
- **CryptoService::normalizeKey()** correctly converts hex to binary for OpenSSL
- Tests confirm hex keys work correctly for both encryption and decryption

### session_id Type
- **buildQrCode** stores `session_id` as integer in QR envelope
- **VerificationService** casts both sides to `(int)` for comparison
- Both integer and string session_id work due to casting

## Test Results

All custom tests pass:
- `test_full_flow.php`: PASS - Encryption/decryption works
- `test_registration_flow.php`: PASS - RegistrationService payload decrypts correctly
- `test_qrtoken_flow.php`: PASS - QrTokenService payload decrypts correctly
- `test_student_lookup.php`: PASS - Student lookup and identity match works
- `test_actual_flow.php`: PASS - Full RegistrationService -> buildQrCode -> verifyQr flow works
- `test_mismatch.php`: PASS - Identified payload structure differences but both contain required fields
- `test_key_format.php`: PASS - Key normalization works correctly, mismatch causes HMAC failure (expected)
- `test_session_id_type.php`: PASS - session_id type handling works correctly

## Possible Root Causes

Since all cryptographic and structural tests pass, the issue is likely:

1. **Database state**: Student record not created or deleted before verification
2. **Token not found**: Token_id mismatch between QR and database
3. **Session inactive**: Exam session marked as inactive
4. **Race condition**: Token status changed between generation and verification
5. **Key mismatch in database**: Keys stored in exam_sessions table corrupted or in wrong format

## Next Steps

Need to examine actual database state and logs to identify which specific check is failing in production.
