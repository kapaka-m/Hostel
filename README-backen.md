# Backend (Laravel API + Web Panel)

## Requirements

- PHP 8.2+
- Composer
- MySQL 8

## Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

### MySQL via Docker

From the repository root:

```bash
docker compose up -d
```

Update `.env` to match your MySQL credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostel
DB_USERNAME=root
DB_PASSWORD=
```

### Migrate and Seed

```bash
php artisan migrate --seed
```

### Run the Server

```bash
php artisan serve
```

## Web Admin Panel

- Login: `http://localhost:8000/login`
- University Admin dashboard: `/admin/university`
- Dorm Admin dashboard: `/admin/dorm`

## API Base URL

- `http://localhost:8000/api`

## Seeded Accounts

- University Admin: `uniadmin@test.com` / `Uni@12345`
- Dorm Admin: `dormadmin@test.com` / `Dorm@12345`
- Student: `student1@test.com` / `Stud@12345`

## Key Features

- Sanctum token auth for mobile
- Session auth for web panel
- Role-based authorization middleware
- Room assignment with capacity enforcement and auto status updates

## Feature Flags (Phase 1)

Feature flags live in the `system_settings` table with keys in the `feature.*` namespace.
Defaults are OFF to keep production behavior unchanged.

Example toggle:

```bash
php artisan tinker
>>> \App\Models\SystemSetting::updateOrCreate(['key' => 'feature.audit_logs'], ['value' => '1', 'type' => 'bool']);
```

Common flags:

- `feature.audit_logs`
- `feature.activity_feed`
- `feature.permissions`
- `feature.correlation_ids`
- `feature.response_time_logging`
- `feature.user_freeze`
- `feature.strong_passwords`
- `feature.admin_ip_allowlist`
- `feature.suspicious_login_alerts`

Admin IP allowlist:

- `security.admin_ip_allowlist` (JSON array, e.g. `["127.0.0.1"]`)

## New Phase 1 Endpoints

- `GET /api/v2/activity-feed` (UNIVERSITY_ADMIN + `feature.activity_feed`)
- `GET /api/v2/audit-logs` (UNIVERSITY_ADMIN + `feature.audit_logs`)

## Admin Pages (Feature-Flagged)

- `/admin/university/activity-feed`
- `/admin/university/audit-logs`
