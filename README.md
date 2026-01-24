# dorm_management_full (Hostel)

Developer: MOHAMED (KAPAKA)

## Overview

Full-stack dorm management system with Laravel API + Blade admin panel and Flutter mobile app.
Phase updates are tracked in `CHANGELOG.md`.

## Structure

- `backend/` Laravel 11 + Sanctum API + Blade admin panel
- `mobile/` Flutter Android app
- `docker-compose.yml` MySQL service

## Quick Start

1. Start MySQL with Docker in the project root:

   ```bash
   docker compose up -d
   ```

2. Follow `backend/README.md` to set up the API and web panel.
3. Follow `mobile/README.md` to run the Flutter app.

## Seeded Accounts

- University Admin: `uniadmin@test.com` / `Uni@12345`
- Dorm Admin: `dormadmin@test.com` / `Dorm@12345`
- Student: `student1@test.com` / `Stud@12345`
