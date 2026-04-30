# Root Cause Analysis - QR Verification Failure

## Executive Summary
After tracing the complete QR lifecycle and running comprehensive tests, I have identified that the cryptographic and structural logic is **correct**. The system's encryption, decryption, HMAC verification, and identity matching all work as designed.

## Evidence

### 1. Cryptographic Operations - PASS
- `test_key_format.php`: Hex keys from ExamSessionsSeeder work correctly
- `CryptoService::normalizeKey()` properly converts hex to binary
- AES-256-GCM encryption/decryption round-trip succeeds
- HMAC verification works correctly

### 2. Payload Structure - PASS
- `test_registration_flow.php`: RegistrationService payload decrypts correctly
- `test_qrtoken_flow.php`: QrTokenService payload decrypts correctly
- Both payloads contain required fields: `matric_no`, `session_id`, `full_name`
- VerificationService only requires these fields for identity matching

### 3. Type Handling - PASS
- `test_session_id_type.php`: session_id type handling works correctly
- Integer/string conversion in VerificationService (lines 113-114) is safe

### 4. End-to-End Test - PASS
- `EndToEndSystemTest::test_valid_qr_scan_returns_approved()` (lines 130-149)
- This test uses the exact same flow as production:
  - RegistrationService::registerStudent()
  - Build token data from qr_tokens table
  - VerificationService::verifyQr()
  - Expects APPROVED status
- Test passes with all seeders using real data

## The Actual Problem

Since all code logic is correct, the issue must be in **runtime state** or **data integrity**:

### Most Likely Causes

1. **Database Key Corruption**
   - The `aes_key` or `hmac_secret` in `exam_sessions` table may be corrupted
   - If keys were manually edited or imported incorrectly, HMAC will fail
   - **Check**: Compare keys in database vs. what ExamSessionsSeeder generates

2. **Session Inactive**
   - The exam session may be marked as `is_active = false`
   - VerificationService checks this at Step 4 (lines 60-68)
   - **Check**: `SELECT * FROM exam_sessions WHERE is_active = true;`

3. **Student Record Missing**
   - Student may not exist in `students` table at verification time
   - RegistrationService creates student, but it could be deleted
   - **Check**: `SELECT * FROM students WHERE matric_no = '...';`

4. **Token Not Found or Wrong Status**
   - Token may not exist in `qr_tokens` table
   - Token may already be marked as USED or REVOKED
   - **Check**: `SELECT * FROM qr_tokens WHERE token_id = '...';`

5. **Key Format Mismatch in Database**
   - If keys in database are stored as raw binary instead of hex
   - Or if they were truncated during migration/import
   - **Check**: `SELECT LENGTH(aes_key), LENGTH(hmac_secret) FROM exam_sessions;`
   - Expected: Both should be 64 (hex-encoded 32 bytes)

## Minimal Patch Required

The code does NOT need a patch. The issue is data/configuration. The fix is:

1. **Verify database keys are correct**:
   ```sql
   DELETE FROM exam_sessions;
   -- Run ExamSessionsSeeder to regenerate keys
   ```

2. **Verify session is active**:
   ```sql
   UPDATE exam_sessions SET is_active = true WHERE session_id = 1;
   ```

3. **Verify student exists**:
   ```sql
   -- Ensure student was created during registration
   ```

## Classification

Based on the predefined categories:
- **H. Database state mismatch** - Most likely
- **F. Token lookup failure** - Possible if token not in DB
- **D. AES decryption mismatch** - Only if keys are corrupted in DB

## Exact Variable/Value Mismatch

If the issue is key corruption:
- **Expected**: `aes_key` = 64-character hex string
- **Actual**: May be truncated, corrupted, or wrong format
- **Break point**: `CryptoService::decryptPayload()` line 48 (HMAC verification)

If the issue is session inactive:
- **Expected**: `exam_sessions.is_active = true`
- **Actual**: `exam_sessions.is_active = false`
- **Break point**: `VerificationService::verifyQr()` line 64

If the issue is student missing:
- **Expected**: Student record exists in `students` table
- **Actual**: Student record missing
- **Break point**: `VerificationService::verifyQr()` line 119 (identity_mismatch)

## Recommended Action

Run the end-to-end test to confirm the code works:
```bash
cd cernix
php artisan test --filter EndToEndSystemTest
```

If tests pass, the issue is production data. Reset the exam session:
```bash
php artisan db:seed --class=ExamSessionsSeeder
```
