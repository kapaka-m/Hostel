# Mobile (Flutter)

## Requirements

- Flutter SDK 3.22+

## Setup

```bash
cd mobile
flutter pub get
```

## Configure API Base URL

Edit `lib/src/config/app_config.dart`:

- Android emulator: `http://10.0.2.2:8000`
- Real device: use your LAN IP, e.g. `http://192.168.1.10:8000`

## Run

```bash
flutter run
```

## Seeded Accounts

- University Admin: `uniadmin@test.com` / `Uni@12345`
- Dorm Admin: `dormadmin@test.com` / `Dorm@12345`
- Student: `student1@test.com` / `Stud@12345`
