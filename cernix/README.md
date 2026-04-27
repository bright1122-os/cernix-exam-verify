# CERNIX — Laravel Application

This directory contains the Laravel 11 application source for the CERNIX Exam Verification System.

For full project documentation — architecture, API reference, security model, demo flow, and test coverage — see the [root README](../README.md).

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret          # then rename JWT_SECRET → APP_JWT_SECRET in .env
php artisan migrate
php artisan db:seed
php artisan serve
```

Visit `http://localhost:8000` to open the home page.

## Key directories

| Path | Contents |
|------|----------|
| `app/Services/` | CryptoService, QrTokenService, VerificationService, RegistrationService, AuditService, RemitaService, MockSISService |
| `app/Http/Controllers/Web/` | StudentWebController, ExaminerWebController, AdminWebController |
| `resources/views/` | Blade views — student/register, examiner/dashboard, admin/dashboard, home, layouts/app |
| `database/migrations/` | 9 domain migrations |
| `database/seeders/` | Departments, ExamSessions, MockSIS, Examiners |
| `tests/` | 113 tests · 294 assertions |

## Running tests

```bash
php artisan test
```
