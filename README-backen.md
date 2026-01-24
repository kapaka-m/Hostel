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
