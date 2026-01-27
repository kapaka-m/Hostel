# Hostel Mobile

Lightweight Flutter client for the existing Larave backend. Material 3 styling, responsive layouts, and explicit role-based guards keep the experience professional across Android, Windows, and Web targets.

## Structure
- `lib/src/app.dart` wires up providers, routing, and theming.
- `lib/src/api` contains the Dio client, endpoint definitions, response parsing, and repositories.
- `lib/src/auth` manages auth state and token storage (secure on mobile/desktop, shared prefs on web).
- `lib/src/models` maps backend resources.
- `lib/src/providers` implements ChangeNotifier-backed feature state for each role.
- `lib/src/routing` defines go_router guards and role-aware shells.
- `lib/src/ui` has reusable widgets and Material screens tailored for UNIVERSITY_ADMIN, DORM_ADMIN, and STUDENT.
- `lib/src/theme` holds Material 3 theme configuration.
- `scripts/clean.*` reset generated build outputs safely before fetching packages.

## Backend integration
- Auth endpoints: `POST /api/login`, `GET /api/me`, `POST /api/logout` (token stored via FlutterSecureStorage/shared prefs depending on platform).
- Student APIs: `/api/student/my-room`, `/api/v1/announcements`.
- Dorm admin APIs: `/api/floors`, `/api/rooms`, `/api/rooms/{room}/assign-student`, `/api/rooms/{room}/occupants`, `/api/students`, `/api/v1/tickets`, `/api/v1/announcements`.
- University admin APIs: `/api/dorms`, `/api/dorms/{dorm}/create-dorm-admin`, `/api/v1/reports/overview`, `/api/v1/settings`, `/api/v2/activity-feed`, `/api/v2/audit-logs`, `/api/v1/tickets`, `/api/v1/announcements`.
- Validation responses include `message` + `errors`; 401 triggers session reset and route redirect; `X-Correlation-Id` headers are logged in debug mode when available.
- Feature-flagged modules (audit logs, activity feed) are hidden unless enabled in settings.

## Running
```bash
flutter pub get
flutter run
```
The app is configured for Android emulators (10.0.2.2:8000), Windows (127.0.0.1:8000), and Web (localhost:8000). Override the API with `--dart-define=API_BASE_URL=http://...` when needed.

## Cleaning
Use the provided scripts before committing to remove generated build artifacts and stale caches.
```powershell
scripts\clean.ps1
```
```bash
./scripts/clean.sh
```

## Testing & Builds
The following commands should run from the `mobile` directory:
- `flutter pub get`
- `dart analyze`
- `flutter test`
- `flutter build web`
- `flutter build windows`
- `flutter build apk`

Adjust build flags locally if the environment needs custom keystores or SDK licenses.
