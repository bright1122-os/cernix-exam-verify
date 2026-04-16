# CERNIX — Exam Verification System

> **Last updated:** Phase 2 complete — Remita payment verification layer  
> **Test suite:** 63 tests · 155 assertions · all passing

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Design](#database-design)
4. [API Endpoints](#api-endpoints)
5. [Services](#services)
6. [Security Model](#security-model)
7. [Environment Variables](#environment-variables)
8. [Installation & Setup](#installation--setup)
9. [Running Tests](#running-tests)
10. [Development Progress](#development-progress)

---

## Project Overview

CERNIX is a **secure exam-hall identity and access verification system** for higher institutions. It solves the problem of impersonation and proxy examination by giving each registered, fee-paying student a one-time cryptographic QR token that an examiner scans at the hall entrance to approve or reject entry.

### The problem it solves

| Problem | CERNIX solution |
|---------|----------------|
| Proxy exam-taking (impersonation) | QR token embeds student identity encrypted with session-specific AES-256-GCM key; examiner sees student photo on scan |
| Token reuse / duplication | Every token is single-use; a second scan returns `DUPLICATE` and logs the event |
| Token forgery | HMAC-SHA256 signature over the encrypted blob; any tamper causes rejection before decryption |
| Unverified payment | Payment RRR verified against Remita before a token is issued |
| Audit trail gaps | Every verification decision is written to `verification_logs`; all system actions to `audit_log` |

### Actors

| Actor | Role |
|-------|------|
| **Student** | Self-registers, pays the exam fee, receives a QR token |
| **Examiner** | Scans QR at the hall; sees APPROVED / DUPLICATE / REJECTED |
| **Admin** | Manages sessions, examiners, and the exam configuration |

---

## System Architecture

### High-level data flow

```
mock_sis (SIS)
    │
    │  MockSISService.getStudentByMatric()
    ▼
Student Registration          ← (next phase)
    │  creates row in students
    ▼
Payment (Remita)              ← (next phase)
    │  verifies RRR, writes payment_records
    ▼
QrTokenService.issue()
    │  encrypts payload with exam_session AES key
    │  signs with HMAC secret
    │  stores in qr_tokens (status = UNUSED)
    │  returns SVG QR image
    ▼
Student presents QR at hall
    ▼
QrTokenService.verify()
    │  decodes QR JSON → fetches session keys
    │  verifies HMAC (tamper check)
    │  decrypts payload (AES-256-GCM)
    │  checks qr_tokens.status
    │  UNUSED  → APPROVED  → marks USED, writes verification_log
    │  USED    → DUPLICATE → writes verification_log
    │  REVOKED → REJECTED  → writes verification_log
    ▼
verification_logs + audit_log
```

### Authentication flow

```
POST /api/{role}/login
    │
    ├─ AuthService.attemptLogin(credentials, role)
    │       ├─ Auth::guard('api')->attempt(credentials)   [JWT driver]
    │       ├─ Verify user.role === requested role
    │       └─ If mismatch → logout + return false → 401
    │
    └─ On success → return { token, token_type: "bearer", expires_in, user }

POST /api/student/register   (students only)
    ├─ Validate name, email, password, phone
    ├─ User::create([..., role: 'student'])
    └─ Auth::guard('api')->login($user) → token

Protected routes: Authorization: Bearer <token>
    └─ auth:api middleware → JWTAuth → resolves User model
```

Roles are embedded as a **custom JWT claim** (`role`) so middleware can gate
role-specific routes without a database hit.

### Component interaction diagram

```
┌─────────────────────────────────────────────────────────┐
│                     HTTP Layer                          │
│  routes/api.php → {Student|Examiner|Admin}\AuthController│
└────────────────────────┬────────────────────────────────┘
                         │
          ┌──────────────▼──────────────┐
          │         AuthService         │
          │  JWT guard · role check     │
          └──────────────┬──────────────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
         ▼               ▼               ▼
  MockSISService   CryptoService   QrTokenService
  (read-only SIS)  (AES-GCM +     (issue · verify
                    HMAC-SHA256)    revoke · QR SVG)
         │               │               │
         └───────────────┴───────────────┘
                         │
          ┌──────────────▼──────────────┐
          │          Database           │
          │  MySQL (prod) / SQLite (test)│
          └─────────────────────────────┘
```

### Security layers

| Layer | Mechanism |
|-------|-----------|
| Transport | HTTPS (production) |
| Authentication | JWT (HS256), signed with `APP_JWT_SECRET`, `auth:api` middleware |
| Role enforcement | Role embedded in JWT claim; `AuthService` rejects cross-role logins |
| Payload encryption | AES-256-GCM with per-session random key stored in `exam_sessions.aes_key` |
| Payload integrity | HMAC-SHA256 of the base64 ciphertext blob; verified before decryption |
| IV randomness | 12-byte random IV generated fresh for every `encryptPayload` call |
| One-time tokens | `qr_tokens.status` transitions `UNUSED → USED`; second scan is `DUPLICATE` |
| Constant-time compare | All HMAC checks use `hash_equals()` — immune to timing attacks |
| Audit trail | Every verification written to `verification_logs`; broader events to `audit_log` |
| No cascade deletes | `verification_logs` and `audit_log` are append-only by design |

---

## Database Design

### Entity relationships

```
departments ◄──── students ────► exam_sessions
                     │                 │
                     │                 │
              payment_records    qr_tokens ◄──── verification_logs
                                       │                │
                                  exam_sessions    examiners

audit_log  (standalone, no FK)
mock_sis   (standalone SIS mirror, no FK)
```

### Tables

#### `departments`
| Column | Type | Notes |
|--------|------|-------|
| dept_id | bigIncrements | PK |
| dept_name | string | e.g. "Computer Science" |
| faculty | string | e.g. "Faculty of Computing" |

#### `exam_sessions`
| Column | Type | Notes |
|--------|------|-------|
| session_id | bigIncrements | PK |
| semester | string | e.g. "First Semester" |
| academic_year | string | e.g. "2025/2026" |
| fee_amount | decimal(10,2) | Exam fee in Naira |
| aes_key | text | 64-char hex of 32 random bytes |
| hmac_secret | text | 64-char hex of 32 random bytes |
| is_active | boolean | Only one session active at a time |
| timestamps | — | created_at, updated_at |

#### `mock_sis`
Simulated Student Information System — read-only source of truth for student identity.

| Column | Type | Notes |
|--------|------|-------|
| matric_no | string | PK |
| full_name | string | |
| department | string | |
| photo_path | string | Relative storage path |

#### `students`
Enrolled students who have been verified against the SIS.

| Column | Type | Notes |
|--------|------|-------|
| matric_no | string | PK, FK → mock_sis |
| full_name | string | Copied from SIS at enrollment |
| department_id | unsignedBigInteger | FK → departments |
| session_id | unsignedBigInteger | FK → exam_sessions |
| photo_path | string | |
| created_at | timestamp | |

#### `payment_records`
| Column | Type | Notes |
|--------|------|-------|
| payment_id | bigIncrements | PK |
| student_id | string | FK → students.matric_no |
| rrr_number | string | UNIQUE — Remita Retrieval Reference |
| amount_declared | decimal(10,2) | Declared by student |
| amount_confirmed | decimal(10,2) | Confirmed by Remita |
| remita_response | json | Full Remita API response |
| verified_at | timestamp | |

#### `examiners`
| Column | Type | Notes |
|--------|------|-------|
| examiner_id | bigIncrements | PK |
| full_name | string | |
| username | string | UNIQUE |
| password_hash | string | bcrypt |
| role | enum | `examiner` \| `admin` |
| is_active | boolean | Default false |
| created_at | timestamp | |

#### `qr_tokens`
| Column | Type | Notes |
|--------|------|-------|
| token_id | char(36) | PK — UUID, no auto-increment |
| student_id | string | FK → students.matric_no |
| session_id | unsignedBigInteger | FK → exam_sessions |
| encrypted_payload | text | Base64(IV \| ciphertext \| tag) |
| hmac_signature | text | HMAC-SHA256 of encrypted_payload |
| status | enum | `UNUSED` \| `USED` \| `REVOKED` |
| issued_at | timestamp | |
| used_at | timestamp | Nullable |

#### `verification_logs`
Append-only — no cascade delete.

| Column | Type | Notes |
|--------|------|-------|
| log_id | bigIncrements | PK |
| token_id | char(36) | FK → qr_tokens |
| examiner_id | unsignedBigInteger | FK → examiners |
| decision | enum | `APPROVED` \| `REJECTED` \| `DUPLICATE` |
| timestamp | timestamp | |
| device_fp | string | Device fingerprint |
| ip_address | string | |

#### `audit_log`
Append-only — no FK, no cascade delete.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| actor_id | string | |
| actor_type | string | e.g. "student", "examiner", "system" |
| action | string | e.g. "token.issued" |
| metadata | json | Arbitrary context payload |
| timestamp | timestamp | |

---

## API Endpoints

Base URL: `/api`

### Student auth
| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| POST | `/student/register` | Public | Register a new student account |
| POST | `/student/login` | Public | Login and receive JWT |

### Examiner auth
| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| POST | `/examiner/login` | Public | Examiner login |

### Admin auth
| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| POST | `/admin/login` | Public | Admin login |

### Shared protected
| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| POST | `/auth/logout` | Bearer | Invalidate JWT |
| POST | `/auth/refresh` | Bearer | Rotate JWT |
| GET | `/auth/me` | Bearer | Current authenticated user |

### Response envelope
All responses follow:
```json
{
  "status": "success | error",
  "message": "...",
  "data": { ... }
}
```

---

## Services

### `AuthService`
`app/Services/AuthService.php`

| Method | Description |
|--------|-------------|
| `attemptLogin(credentials, role)` | Verifies credentials **and** role match; returns JWT string or `false` |
| `logout()` | Invalidates current JWT via guard |
| `refresh()` | Rotates token, returns new JWT string |
| `me()` | Returns the authenticated `User` model |
| `tokenPayload(token)` | Builds `{token, token_type, expires_in}` response array |

---

### `CryptoService`
`app/Services/CryptoService.php`

| Method | Description |
|--------|-------------|
| `encryptPayload(payload, aesKey, hmacSecret)` | AES-256-GCM encrypt + HMAC-SHA256 sign |
| `decryptPayload(encrypted, hmac, aesKey, hmacSecret)` | HMAC verify first, then GCM decrypt |
| `generateRandomKey(length)` | `bin2hex(random_bytes(n))` — default 32 bytes |
| `signData(data, hmacSecret)` | HMAC-SHA256 |
| `verifySignature(data, signature, hmacSecret)` | Constant-time `hash_equals` compare |

**Encrypted blob layout:** `base64( IV[12 bytes] | ciphertext | auth_tag[16 bytes] )`

Keys accepted as either raw 32-byte strings or 64-char hex (as stored in `exam_sessions`).

---

### `QrTokenService`
`app/Services/QrTokenService.php`

| Method | Description |
|--------|-------------|
| `issue(matricNo, sessionId)` | Validates student + session, prevents duplicate UNUSED tokens, encrypts payload, stores in `qr_tokens`, returns token data + SVG QR |
| `verify(qrContent, examinerId, deviceFp, ip)` | Full verify pipeline → `APPROVED / DUPLICATE / REJECTED` → logs to `verification_logs` |
| `revoke(tokenId)` | Transitions `UNUSED → REVOKED` |
| `buildQrCode(content, size)` | Returns SVG string (no Imagick required) |

**QR content (encoded in image):**
```json
{ "v": 1, "session_id": 1, "encrypted_payload": "...", "hmac_signature": "..." }
```

---

### `MockSISService`
`app/Services/MockSISService.php`  
**Read-only.** No writes permitted.

| Method | Description |
|--------|-------------|
| `getStudentByMatric(matricNo)` | Returns `{matric_no, full_name, department, photo_path}` or throws `"Student not found in SIS"` |
| `getPhotoPath(matricNo)` | Returns `photo_path` string only |

---

### `RemitaService`
`app/Services/RemitaService.php`

Wraps the **Remita Fintech payment-query API**. All credentials are read from environment variables at runtime — never hardcoded.

| Method | Description |
|--------|-------------|
| `verifyPayment(rrrNumber, expectedAmount)` | Full pipeline: duplicate-RRR guard → Remita API call → success check → amount match. Returns full response array or throws. |
| `isPaymentSuccessful(response)` | Returns `true` when Remita status is `"Payment Successful"` (case-insensitive) or `"00"` |
| `amountMatches(expected, actual)` | Safe float comparison within a 0.001 tolerance |
| `rrrAlreadyUsed(rrrNumber)` | Checks `payment_records.rrr_number` — prevents replay attacks |

#### Remita credentials

| Env variable | Description |
|---|---|
| `REMITA_BASE_URL` | e.g. `https://remitademo.net/remita/exapp/api/v1` |
| `REMITA_PUBLIC_KEY` | Your Remita Fintech public key — used as `remitaConsumerKey` in the Authorization header |
| `REMITA_SECRET_KEY` | Your Remita Fintech secret key — used **only** to derive the `remitaConsumerToken` via `SHA512(publicKey + rrr + secretKey)`; **never sent over the wire** |

Set these in your local `.env` file. The `.env.example` has placeholder entries. Never commit real keys.

#### API call details

```
GET {REMITA_BASE_URL}/payment/query/{rrr}
Authorization: remitaConsumerKey={publicKey},remitaConsumerToken={sha512(publicKey+rrr+secretKey)}
Content-Type: application/json
```

#### How payment verification fits the system flow

```
Student submits RRR
        │
        ▼
RemitaService.verifyPayment(rrr, expectedAmount)
        ├─ rrrAlreadyUsed()  → throws if RRR already in payment_records
        ├─ queryRemita()     → hits Remita API, returns JSON body
        ├─ isPaymentSuccessful() → throws if status is not "Payment Successful"
        └─ amountMatches()   → throws if confirmed amount ≠ expected amount
        │
        ▼ (all checks passed)
Caller stores row in payment_records
        └─ remita_response = full JSON body
        └─ amount_confirmed = body['amount']
        └─ rrr_number = rrr (now guarded against reuse)
        │
        ▼
QrTokenService.issue() — token can now be issued
```

---

## Security Model

### Key management
- `exam_sessions.aes_key` and `exam_sessions.hmac_secret` are generated with `bin2hex(random_bytes(32))` at session creation — **never hardcoded**.
- `APP_JWT_SECRET` is set once per deployment via `php artisan jwt:secret` — stored only in `.env`.

### Encryption scheme
```
plaintext JSON payload
        │
        ▼
AES-256-GCM (random 12-byte IV, 128-bit auth tag)
        │
        ▼
base64( IV | ciphertext | tag )   ← encrypted_payload
        │
        ▼
HMAC-SHA256(encrypted_payload, hmac_secret)  ← hmac_signature
```

Verification reverses: HMAC checked first (constant-time), then GCM decryption (tag authenticates ciphertext). A forged or modified payload is rejected before any decryption is attempted.

### Token lifecycle
```
ISSUED → UNUSED
              │
    first scan│
              ▼
           USED  (decision: APPROVED)
              │
  rescan same │
              ▼
        DUPLICATE log entry (status stays USED)

    or
              │
  admin revoke│
              ▼
          REVOKED  (decision: REJECTED on any scan)
```

---

## Environment Variables

```ini
APP_NAME=CERNIX
APP_KEY=           # Set by: php artisan key:generate
APP_JWT_SECRET=    # Set by: php artisan jwt:secret (then rename JWT_SECRET → APP_JWT_SECRET)
APP_URL=http://localhost
APP_ENV=local
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cernix
DB_USERNAME=root
DB_PASSWORD=

REMITA_BASE_URL=https://remitademo.net/remita/exapp/api/v1
REMITA_MERCHANT_ID=
REMITA_SERVICE_TYPE_ID=
REMITA_API_KEY=
```

---

## Installation & Setup

```bash
# 1. Clone the repository
git clone https://github.com/bright1122-os/cernix-exam-verify.git
cd cernix-exam-verify/cernix

# 2. Install dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate
# Generate JWT secret, then move it to APP_JWT_SECRET in .env
php artisan jwt:secret

# 4. Database (MySQL)
# Create the 'cernix' database, then:
php artisan migrate
php artisan db:seed

# 5. Run the application
php artisan serve
```

### Seed data included
| Seeder | Records |
|--------|---------|
| `DepartmentsSeeder` | 5 departments — Faculty of Computing |
| `ExamSessionsSeeder` | 1 active session — First Semester 2025/2026, ₦10,000 |
| `MockSISSeeder` | 5 students (realistic Nigerian names) |
| `ExaminersSeeder` | `examiner1` (password: `password123`) + `admin1` (password: `admin123`) |

---

## Running Tests

```bash
# All tests
php artisan test

# Specific suite
php artisan test tests/Feature/QrTokenServiceTest.php
php artisan test tests/Unit/CryptoServiceTest.php
```

### Current test coverage

| Test file | Tests | Assertions | Covers |
|-----------|-------|-----------|--------|
| `AppTest` | 1 | 1 | Root route |
| `AuthTest` | 12 | 46 | JWT auth, role enforcement |
| `DatabaseSchemaTest` | 1 | 9 | All 9 domain tables exist |
| `SeederTest` | 2 | 3 | Active session count, SIS records |
| `CryptoServiceTest` | 10 | 17 | AES-GCM, HMAC, key generation |
| `QrTokenServiceTest` | 17 | 33 | Issue, verify, revoke, QR image |
| `MockSISServiceTest` | 7 | 13 | SIS lookup, photo path, error cases |
| `RemitaServiceTest` | 11 | 31 | Payment verify, amount match, duplicate RRR |
| **Total** | **63** | **155** | |

---

## Development Progress

### Completed

| Phase | Description |
|-------|-------------|
| Skeleton | Laravel 11 project, packages, folder structure, `.env.example` |
| Auth | JWT auth for student / examiner / admin roles |
| Schema | 9 domain migrations with full FK constraints |
| Seeds | Departments, exam session, mock SIS students, examiners |
| CryptoService | AES-256-GCM encryption + HMAC-SHA256 signing layer |
| QrTokenService | Token issuance, one-time verification, revocation, SVG QR generation |
| MockSISService | Read-only SIS lookup by matric number |
| RemitaService | Remita Fintech payment verification — RRR query, amount check, duplicate guard |

### Up next

| Phase | Description |
|-------|-------------|
| Student Registration | Enrol a SIS-verified student into an active exam session |
| Payment Flow | Wire `RemitaService.verifyPayment()` into the registration controller and write `payment_records` |
| Examiner API | Protected endpoints for QR scan submission and verification result |
| Admin API | Session management, examiner management, token revocation |
| Audit Logging | Write to `audit_log` on key system events |
