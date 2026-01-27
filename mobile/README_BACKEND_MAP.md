# 1) Environment & Framework
- Laravel: 11.48.0 (php artisan --version).
- PHP: 8.2.12 (ZTS) (php -v).
- Composer: 2.9.2 (composer -V).
- App summary (php artisan about):
  - Application Name: Hostel
  - Environment: local
  - Debug: ENABLED
  - URL: localhost:8000
  - Timezone: UTC
  - Locale: en
  - Cache: config NOT cached, events NOT cached, routes CACHED, views CACHED
  - Drivers: broadcast=log, cache=database, database=mysql, logs=stack/single, mail=log, queue=database, session=database
  - Spatie Permissions: version 6.24.0
- Auth config (php artisan config:show auth / config/auth.php):
  - Default guard: web (session)
  - API guard: sanctum
  - Provider: users -> App\Models\User
- Sanctum config (php artisan config:show sanctum / config/sanctum.php):
  - Stateful domains include localhost + 127.0.0.1
  - Guard: web
  - Token expiration: null (no TTL)
- Session config (php artisan config:show session / config/session.php):
  - Driver: database
  - Cookie: hostel_session
  - SameSite: lax

# 2) Auth: Guards, Tokens, Sessions, Headers
- Web auth (session, guard web):
  - GET /login -> Web\AuthController@showLogin
  - POST /login -> Web\AuthController@login (throttle:login, 5/min from AppServiceProvider)
  - POST /logout -> Web\AuthController@logout
  - Students are blocked from admin UI after login and logged out (see Web\AuthController).
- API auth (Sanctum tokens, guard sanctum):
  - POST /api/login -> Api\AuthController@login uses Auth::attempt and returns { token, user }.
    - Token created via $user->createToken('mobile')->plainTextToken.
  - GET /api/me -> Api\AuthController@me returns UserResource.
  - POST /api/logout -> deletes current access token.
- Required headers for API:
  - Authorization: Bearer <token> for authenticated API routes.
  - Accept: application/json recommended to ensure JSON error bodies.
- CSRF for SPA (Sanctum): GET /sanctum/csrf-cookie sets XSRF cookie (web middleware).
- Correlation IDs (feature-flagged):
  - Middleware CorrelationIdMiddleware looks for X-Correlation-Id and generates one if missing.
  - Echoes back in response header X-Correlation-Id.
  - Enabled only when feature flag correlation_ids is true.
- User freeze checks (feature-flagged):
  - EnsureUserActive blocks inactive/frozen users with 403 JSON { message: "Account is inactive.", errors: [] }.
  - For web, it logs out and redirects to login with error.
- Admin IP allowlist (feature-flagged):
  - AdminIpAllowlist on /admin/** uses system_settings.security.admin_ip_allowlist.
  - If enabled and IP not in allowlist: 403 (JSON or abort).

# 3) Roles & Permissions (with exact role strings)
Roles (from App\Models\User):
- SUPER_ADMIN
- UNIVERSITY_ADMIN
- DORM_ADMIN
- STUDENT

Role enforcement:
- Route middleware role:... -> App\Http\Middleware\RoleMiddleware.
  - SUPER_ADMIN bypasses role checks.
  - If feature flag permissions is enabled AND user has Spatie roles, it checks hasAnyRole().
  - Otherwise checks users.role column.
  - JSON errors: 401 Unauthenticated. or 403 Forbidden..

Spatie permissions (optional, feature-flagged):
- Tables: roles, permissions, model_has_roles, model_has_permissions, role_has_permissions.
- Seeded permissions (Database\Seeders\PermissionsSeeder):
  - manage_dorms, manage_dorm_admins, manage_floors, manage_rooms, assign_students,
    manage_students, view_reports, view_audit_logs, view_activity_feed.
- Seeded role mapping:
  - UNIVERSITY_ADMIN: all permissions
  - DORM_ADMIN: manage_floors, manage_rooms, assign_students, manage_students, view_reports
  - STUDENT: none
- Command: php artisan users:sync-roles syncs Spatie roles from users.role.

Policies / Gates (registered in AppServiceProvider):
- DormPolicy: university admins manage dorms in their university; dorm admins can only view their dorm; super admin bypass.
- FloorPolicy / RoomPolicy: university admins manage floors/rooms within their university; dorm admins within their dorm; super admin bypass.
- StudentPolicy: university admins manage students in their university; dorm admins in their dorm; students can view own record; super admin bypass.
- RoomAssignmentPolicy: university admins and dorm admins within scope; super admin bypass.
- TicketPolicy / AnnouncementPolicy: university admins within university; dorm admins within dorm; super admin bypass.

Feature flags (config/feature-flags.php + SystemSetting):
- Defaults (all false): audit_logs, activity_feed, permissions, correlation_ids, response_time_logging,
  user_freeze, strong_passwords, admin_ip_allowlist, suspicious_login_alerts.

# 4) Feature Inventory (grouped by role + shared)

## Route Catalog (API + Web, grouped by role)
## API_PUBLIC
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| POST | /api/login | generated::D2FPcbLtIHtpvjUE | App\Http\Controllers\Api\AuthController@login | api |

## API_AUTH_SHARED
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| POST | /api/logout | generated::JijLKx7Bbt8hoTHy | App\Http\Controllers\Api\AuthController@logout | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/me | generated::s1MLxlVP4IrHz6bb | App\Http\Controllers\Api\AuthController@me | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/announcements | generated::oLBCX0wRV5FUoQEZ | App\Http\Controllers\Api\V1\AnnouncementController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| POST | /api/v1/announcements | generated::iEtEpUU2iZgfl5xB | App\Http\Controllers\Api\V1\AnnouncementController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/announcements/{announcement} | generated::UOm0kuDjtwtlFSRS | App\Http\Controllers\Api\V1\AnnouncementController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| PUT | /api/v1/announcements/{announcement} | generated::LbwEkhUrzC0yeYiE | App\Http\Controllers\Api\V1\AnnouncementController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/reports/overview | generated::ItjVLRLGDVEqYZlt | App\Http\Controllers\Api\V1\ReportController@overview | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/settings | generated::HADcni2A9pctg59Q | App\Http\Controllers\Api\V1\SettingsController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| POST | /api/v1/settings | generated::x0Tw037lwGdq7ozk | App\Http\Controllers\Api\V1\SettingsController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/tickets | generated::rMDgHMFI9TYaQwI3 | App\Http\Controllers\Api\V1\TicketController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| POST | /api/v1/tickets | generated::moTPjCMXN909RLyV | App\Http\Controllers\Api\V1\TicketController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| GET, HEAD | /api/v1/tickets/{ticket} | generated::xtI7WR5WSQnaZEDN | App\Http\Controllers\Api\V1\TicketController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| PUT | /api/v1/tickets/{ticket} | generated::No8hY25O09GZwgaP | App\Http\Controllers\Api\V1\TicketController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum |
| POST | /api/v1/tickets/{ticket}/comments | generated::d4keoke0XOlkhXUR | App\Http\Controllers\Api\V1\TicketController@comment | api, Illuminate\Auth\Middleware\Authenticate:sanctum |

## API_UNIVERSITY
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /api/dorms | dorms.index | App\Http\Controllers\Api\DormController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /api/dorms | dorms.store | App\Http\Controllers\Api\DormController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| DELETE | /api/dorms/{dorm} | dorms.destroy | App\Http\Controllers\Api\DormController@destroy | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /api/dorms/{dorm} | dorms.show | App\Http\Controllers\Api\DormController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| PUT, PATCH | /api/dorms/{dorm} | dorms.update | App\Http\Controllers\Api\DormController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /api/dorms/{dorm}/create-dorm-admin | generated::xQtxqOErbLHQKey5 | App\Http\Controllers\Api\DormController@createDormAdmin | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /api/v2/activity-feed | generated::EjZw1ErDYd6ye1n7 | App\Http\Controllers\Api\V2\ActivityFeedController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN, App\Http\Middleware\FeatureFlagMiddleware:activity_feed |
| GET, HEAD | /api/v2/audit-logs | generated::dEW0KNaoZg3ZISbb | App\Http\Controllers\Api\V2\AuditLogController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN, App\Http\Middleware\FeatureFlagMiddleware:audit_logs |

## API_DORM
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /api/floors | floors.index | App\Http\Controllers\Api\FloorController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /api/floors | floors.store | App\Http\Controllers\Api\FloorController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /api/floors/{floor} | floors.destroy | App\Http\Controllers\Api\FloorController@destroy | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/floors/{floor} | floors.show | App\Http\Controllers\Api\FloorController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT, PATCH | /api/floors/{floor} | floors.update | App\Http\Controllers\Api\FloorController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/rooms | rooms.index | App\Http\Controllers\Api\RoomController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /api/rooms | rooms.store | App\Http\Controllers\Api\RoomController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /api/rooms/{room} | rooms.destroy | App\Http\Controllers\Api\RoomController@destroy | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/rooms/{room} | rooms.show | App\Http\Controllers\Api\RoomController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT, PATCH | /api/rooms/{room} | rooms.update | App\Http\Controllers\Api\RoomController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /api/rooms/{room}/assign-student | generated::1gLdANKIOwEIpub3 | App\Http\Controllers\Api\RoomController@assignStudent | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/rooms/{room}/occupants | generated::gCiFxxA3zezhXjO6 | App\Http\Controllers\Api\RoomController@occupants | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/students | students.index | App\Http\Controllers\Api\StudentController@index | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /api/students | students.store | App\Http\Controllers\Api\StudentController@store | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /api/students/{student} | students.destroy | App\Http\Controllers\Api\StudentController@destroy | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /api/students/{student} | students.show | App\Http\Controllers\Api\StudentController@show | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT, PATCH | /api/students/{student} | students.update | App\Http\Controllers\Api\StudentController@update | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |

## API_STUDENT
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /api/student/my-room | generated::a63D5cH5oS6xLwku | App\Http\Controllers\Api\StudentRoomController@myRoom | api, Illuminate\Auth\Middleware\Authenticate:sanctum, App\Http\Middleware\RoleMiddleware:STUDENT |


## WEB_SHARED
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | / | generated::iCQEFvjPkPhmsInv | App\Http\Controllers\Web\HomeController@index | web |
| GET, HEAD | /admin | admin.home | App\Http\Controllers\Web\AdminHomeController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist |
| POST | /admin/notifications/read | admin.notifications.read | App\Http\Controllers\Web\AdminNotificationController@markAllRead | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist |
| POST | /admin/preferences/theme | admin.preferences.theme | App\Http\Controllers\Web\AdminPreferenceController@updateTheme | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist |
| GET, HEAD | /admin/search | admin.search | App\Http\Controllers\Web\AdminSearchController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist |
| GET, HEAD | /login | login | App\Http\Controllers\Web\AuthController@showLogin | web |
| POST | /login | generated::d5VaLJImrMYjJnhR | App\Http\Controllers\Web\AuthController@login | web, Illuminate\Routing\Middleware\ThrottleRequests:login |
| POST | /logout | logout | App\Http\Controllers\Web\AuthController@logout | web |
| GET, HEAD | /sanctum/csrf-cookie | sanctum.csrf-cookie | Laravel\Sanctum\Http\Controllers\CsrfCookieController@show | web |
| GET, HEAD | /storage/{path} | storage.local | Closure |  |
| GET, HEAD | /up | generated::IrGgYNPDMN8jw7hN | Closure |  |

## WEB_SUPER
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /admin/super | admin.super.dashboard | App\Http\Controllers\Web\SuperDashboardController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:SUPER_ADMIN |

## WEB_UNI
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /admin/university | admin.university.dashboard | App\Http\Controllers\Web\UniversityDashboardController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/activity-feed | admin.university.activity-feed.index | App\Http\Controllers\Web\UniversityActivityFeedController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN, App\Http\Middleware\FeatureFlagMiddleware:activity_feed |
| GET, HEAD | /admin/university/activity-feed/data | admin.university.activity-feed.data | App\Http\Controllers\Web\UniversityActivityFeedController@data | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN, App\Http\Middleware\FeatureFlagMiddleware:activity_feed |
| GET, HEAD | /admin/university/announcements | admin.university.announcements.index | App\Http\Controllers\Web\UniversityAnnouncementController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/announcements | admin.university.announcements.store | App\Http\Controllers\Web\UniversityAnnouncementController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/announcements/{announcement} | admin.university.announcements.show | App\Http\Controllers\Web\UniversityAnnouncementController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| PUT | /admin/university/announcements/{announcement} | admin.university.announcements.update | App\Http\Controllers\Web\UniversityAnnouncementController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/announcements/{announcement}/edit | admin.university.announcements.edit | App\Http\Controllers\Web\UniversityAnnouncementController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/announcements/create | admin.university.announcements.create | App\Http\Controllers\Web\UniversityAnnouncementController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/audit-logs | admin.university.audit-logs.index | App\Http\Controllers\Web\UniversityAuditLogController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN, App\Http\Middleware\FeatureFlagMiddleware:audit_logs |
| GET, HEAD | /admin/university/dorm-admins | admin.university.dorm-admins.index | App\Http\Controllers\Web\UniversityDormAdminController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/dorm-admins | admin.university.dorm-admins.store | App\Http\Controllers\Web\UniversityDormAdminController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| PUT | /admin/university/dorm-admins/{dormAdmin} | admin.university.dorm-admins.update | App\Http\Controllers\Web\UniversityDormAdminController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/dorm-admins/{dormAdmin}/edit | admin.university.dorm-admins.edit | App\Http\Controllers\Web\UniversityDormAdminController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/dorm-admins/create | admin.university.dorm-admins.create | App\Http\Controllers\Web\UniversityDormAdminController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/dorms | admin.university.dorms.index | App\Http\Controllers\Web\UniversityDormController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/dorms | admin.university.dorms.store | App\Http\Controllers\Web\UniversityDormController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| DELETE | /admin/university/dorms/{dorm} | admin.university.dorms.destroy | App\Http\Controllers\Web\UniversityDormController@destroy | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| PUT | /admin/university/dorms/{dorm} | admin.university.dorms.update | App\Http\Controllers\Web\UniversityDormController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/dorms/{dorm}/edit | admin.university.dorms.edit | App\Http\Controllers\Web\UniversityDormController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/dorms/create | admin.university.dorms.create | App\Http\Controllers\Web\UniversityDormController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/reports | admin.university.reports.index | App\Http\Controllers\Web\UniversityReportController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/settings | admin.university.settings.index | App\Http\Controllers\Web\UniversitySettingsController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/settings | admin.university.settings.update | App\Http\Controllers\Web\UniversitySettingsController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/students | admin.university.students.index | App\Http\Controllers\Web\UniversityStudentController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/students/export | admin.university.students.export | App\Http\Controllers\Web\UniversityStudentController@export | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/students/import | admin.university.students.import | App\Http\Controllers\Web\UniversityStudentController@import | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/students/import | admin.university.students.import.store | App\Http\Controllers\Web\UniversityStudentController@storeImport | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/tickets | admin.university.tickets.index | App\Http\Controllers\Web\UniversityTicketController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/tickets | admin.university.tickets.store | App\Http\Controllers\Web\UniversityTicketController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/tickets/{ticket} | admin.university.tickets.show | App\Http\Controllers\Web\UniversityTicketController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| PUT | /admin/university/tickets/{ticket} | admin.university.tickets.update | App\Http\Controllers\Web\UniversityTicketController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| POST | /admin/university/tickets/{ticket}/comment | admin.university.tickets.comment | App\Http\Controllers\Web\UniversityTicketController@comment | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/tickets/{ticket}/edit | admin.university.tickets.edit | App\Http\Controllers\Web\UniversityTicketController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/tickets/create | admin.university.tickets.create | App\Http\Controllers\Web\UniversityTicketController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |
| GET, HEAD | /admin/university/tickets/export | admin.university.tickets.export | App\Http\Controllers\Web\UniversityTicketController@export | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:UNIVERSITY_ADMIN |

## WEB_DORM
| Method | Path | Name | Controller | Middleware |
| --- | --- | --- | --- | --- |
| GET, HEAD | /admin/dorm | admin.dorm.dashboard | App\Http\Controllers\Web\DormDashboardController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/announcements | admin.dorm.announcements.index | App\Http\Controllers\Web\DormAnnouncementController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/announcements | admin.dorm.announcements.store | App\Http\Controllers\Web\DormAnnouncementController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/announcements/{announcement} | admin.dorm.announcements.show | App\Http\Controllers\Web\DormAnnouncementController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/announcements/{announcement} | admin.dorm.announcements.update | App\Http\Controllers\Web\DormAnnouncementController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/announcements/{announcement}/edit | admin.dorm.announcements.edit | App\Http\Controllers\Web\DormAnnouncementController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/announcements/create | admin.dorm.announcements.create | App\Http\Controllers\Web\DormAnnouncementController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/assignments | admin.dorm.assignments.index | App\Http\Controllers\Web\DormAssignmentController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/assignments/{assignment} | admin.dorm.assignments.update | App\Http\Controllers\Web\DormAssignmentController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/assignments/{assignment}/move | admin.dorm.assignments.move | App\Http\Controllers\Web\DormAssignmentController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/floors | admin.dorm.floors.index | App\Http\Controllers\Web\DormFloorController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/floors | admin.dorm.floors.store | App\Http\Controllers\Web\DormFloorController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /admin/dorm/floors/{floor} | admin.dorm.floors.destroy | App\Http\Controllers\Web\DormFloorController@destroy | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/floors/{floor} | admin.dorm.floors.update | App\Http\Controllers\Web\DormFloorController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/floors/{floor}/edit | admin.dorm.floors.edit | App\Http\Controllers\Web\DormFloorController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/floors/bulk-delete | admin.dorm.floors.bulk-delete | App\Http\Controllers\Web\DormFloorController@bulkDelete | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/floors/create | admin.dorm.floors.create | App\Http\Controllers\Web\DormFloorController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/rooms | admin.dorm.rooms.index | App\Http\Controllers\Web\DormRoomController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms | admin.dorm.rooms.store | App\Http\Controllers\Web\DormRoomController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /admin/dorm/rooms/{room} | admin.dorm.rooms.destroy | App\Http\Controllers\Web\DormRoomController@destroy | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/rooms/{room} | admin.dorm.rooms.show | App\Http\Controllers\Web\DormRoomController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/rooms/{room} | admin.dorm.rooms.update | App\Http\Controllers\Web\DormRoomController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/{room}/assign | admin.dorm.rooms.assign | App\Http\Controllers\Web\DormRoomController@assignStudent | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/rooms/{room}/edit | admin.dorm.rooms.edit | App\Http\Controllers\Web\DormRoomController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/{room}/restore | admin.dorm.rooms.restore | App\Http\Controllers\Web\DormRoomController@restore | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/{room}/unassign | admin.dorm.rooms.unassign | App\Http\Controllers\Web\DormRoomController@unassignStudent | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/bulk-delete | admin.dorm.rooms.bulk-delete | App\Http\Controllers\Web\DormRoomController@bulkDelete | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/bulk-export | admin.dorm.rooms.bulk-export | App\Http\Controllers\Web\DormRoomController@bulkExport | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/rooms/bulk-restore | admin.dorm.rooms.bulk-restore | App\Http\Controllers\Web\DormRoomController@bulkRestore | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/rooms/create | admin.dorm.rooms.create | App\Http\Controllers\Web\DormRoomController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/rooms/export | admin.dorm.rooms.export | App\Http\Controllers\Web\DormRoomController@export | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/students | admin.dorm.students.index | App\Http\Controllers\Web\DormStudentController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students | admin.dorm.students.store | App\Http\Controllers\Web\DormStudentController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| DELETE | /admin/dorm/students/{student} | admin.dorm.students.destroy | App\Http\Controllers\Web\DormStudentController@destroy | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/students/{student} | admin.dorm.students.show | App\Http\Controllers\Web\DormStudentController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/students/{student} | admin.dorm.students.update | App\Http\Controllers\Web\DormStudentController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/students/{student}/edit | admin.dorm.students.edit | App\Http\Controllers\Web\DormStudentController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/{student}/freeze | admin.dorm.students.freeze | App\Http\Controllers\Web\DormStudentController@freeze | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/{student}/restore | admin.dorm.students.restore | App\Http\Controllers\Web\DormStudentController@restore | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/{student}/unassign | admin.dorm.students.unassign | App\Http\Controllers\Web\DormStudentController@unassign | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/{student}/unfreeze | admin.dorm.students.unfreeze | App\Http\Controllers\Web\DormStudentController@unfreeze | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/bulk-delete | admin.dorm.students.bulk-delete | App\Http\Controllers\Web\DormStudentController@bulkDelete | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/bulk-export | admin.dorm.students.bulk-export | App\Http\Controllers\Web\DormStudentController@bulkExport | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/bulk-freeze | admin.dorm.students.bulk-freeze | App\Http\Controllers\Web\DormStudentController@bulkFreeze | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/bulk-restore | admin.dorm.students.bulk-restore | App\Http\Controllers\Web\DormStudentController@bulkRestore | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/students/bulk-unfreeze | admin.dorm.students.bulk-unfreeze | App\Http\Controllers\Web\DormStudentController@bulkUnfreeze | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/students/create | admin.dorm.students.create | App\Http\Controllers\Web\DormStudentController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/students/export | admin.dorm.students.export | App\Http\Controllers\Web\DormStudentController@export | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/tickets | admin.dorm.tickets.index | App\Http\Controllers\Web\DormTicketController@index | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/tickets | admin.dorm.tickets.store | App\Http\Controllers\Web\DormTicketController@store | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/tickets/{ticket} | admin.dorm.tickets.show | App\Http\Controllers\Web\DormTicketController@show | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| PUT | /admin/dorm/tickets/{ticket} | admin.dorm.tickets.update | App\Http\Controllers\Web\DormTicketController@update | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| POST | /admin/dorm/tickets/{ticket}/comment | admin.dorm.tickets.comment | App\Http\Controllers\Web\DormTicketController@comment | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/tickets/{ticket}/edit | admin.dorm.tickets.edit | App\Http\Controllers\Web\DormTicketController@edit | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/tickets/create | admin.dorm.tickets.create | App\Http\Controllers\Web\DormTicketController@create | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |
| GET, HEAD | /admin/dorm/tickets/export | admin.dorm.tickets.export | App\Http\Controllers\Web\DormTicketController@export | web, Illuminate\Auth\Middleware\Authenticate, App\Http\Middleware\AdminIpAllowlist, App\Http\Middleware\RoleMiddleware:DORM_ADMIN |


## Shared/Core (feature notes)
- Auth (API): Api\AuthController + AuthLoginRequest.
  - POST /api/login: expects email, password; returns { token, user } or 422 on invalid credentials.
  - GET /api/me: returns UserResource {id, university_id, name, email, role}.
  - POST /api/logout: returns { message, errors: [] }.
- Auth (Web): Web\AuthController + WebLoginRequest (email, password, min 6), throttle:login.
- Theme preferences (Web): POST /admin/preferences/theme -> AdminPreferenceController@updateTheme.
  - Validation: theme in light|dark.
  - Response: JSON { message, theme } if expectsJson, otherwise redirects.
- Admin notifications: POST /admin/notifications/read marks all notifications as read.
- Admin search: GET /admin/search (q >= 2 chars). Searches Dorms, Rooms, Students within scope.
- Announcements (API v1): Api\V1\AnnouncementController + AnnouncementRequest.
  - Roles enforced in controller: UNIVERSITY_ADMIN, DORM_ADMIN, STUDENT (read only).
  - Pagination: 20.
  - Response: AnnouncementResource with nested Dorm/User when loaded.
- Tickets (API v1): Api\V1\TicketController + TicketRequest + TicketCommentRequest.
  - Roles enforced in controller: UNIVERSITY_ADMIN, DORM_ADMIN only.
  - Pagination: 20. Comments create TicketCommentResource.
- Reports (API v1): Api\V1\ReportController@overview (University Admin only).
  - Response: totals, trend, ticket_status_counts, dorms arrays.
- Settings (API v1): Api\V1\SettingsController (University Admin only).
  - Reads/writes feature flags and security.admin_ip_allowlist, activity.retention_days.
- Health/Utility:
  - GET /up (health) and GET /storage/{path} (local storage files).
- Universities CRUD:
  - No API or web CRUD routes found for universities (only dashboards/seeders/backfill migration).

### Resource shapes (API)
- UserResource: id, university_id, name, email, role.
- DormResource: id, university_id, code, name, address, capacity, status, contact_name, contact_email, contact_phone, notes.
- FloorResource: id, dorm_id, number, bathrooms, kitchens, showers.
- RoomResource: id, dorm_id, floor_id, room_number, capacity, status, occupancy.
- StudentResource: id, user_id, dorm_id, full_name, student_no, phone, email.
- AnnouncementResource: id, title, body, audience, status, current_status, publish_at, expire_at, created_at, updated_at, dorm, creator.
- TicketResource: id, subject, description, category, priority, status, resolved_at, created_at, updated_at, dorm, creator, assignee, comments.
- TicketCommentResource: id, body, created_at, user.
- AuditLogResource: id, actor{id,name,email,role}, university_id, action, entity_type, entity_id, before, after, ip, user_agent, correlation_id, created_at.

### UI/UX contract hints (queries, pagination, uploads)
- API pagination (Laravel resource collections):
  - /api/v1/announcements and /api/v1/tickets paginate 20.
  - /api/v2/audit-logs paginates 50; /api/v2/activity-feed returns latest 100 (no pagination).
- API filters:
  - /api/rooms: floor_id.
  - /api/v1/announcements: status, audience.
  - /api/v1/tickets: q, status, priority.
- Web filters/sorts (for parity / admin tooling):
  - Dorm rooms: visibility (archived), q, floor_id, status, availability (has_space/full).
  - Dorm students: q, assignment (assigned/unassigned), status (archived).
  - Dorm assignments: q, status (active), room_id.
  - Dorm tickets: q, status, priority.
  - Dorm announcements: q, status.
  - University dorms: q, status, sort (name|capacity|status|created_at), direction (asc|desc).
  - University dorm admins: q.
  - University students: q, dorm_id, status.
  - University tickets: q, status, priority, dorm_id.
  - University announcements: q, status, audience, dorm_id.
  - University audit logs: action, entity_type, entity_id, actor, correlation_id, from, to.
  - University activity feed: q.
- File upload (CSV import):
  - POST /admin/university/students/import expects multipart file field "file" (CSV/TXT, max 5120 KB),
    optional default_dorm_id. CSV headers: full_name, student_no, email; optional phone, dorm_code.

## SUPER_ADMIN capabilities
- Web routes: see WEB_SUPER table.
- Super admin bypasses role middleware and policy checks (RoleMiddleware + ChecksRoles).
- Dashboard shows counts of universities/dorms/users (SuperDashboardController).

## UNIVERSITY_ADMIN capabilities
### Key features
- Dorms CRUD (API + Web):
  - Controllers: Api\DormController, Web\UniversityDormController.
  - Validation: DormRequest (unique code per university, status ACTIVE/INACTIVE, contact fields).
  - Scoping: university_id from current user.
  - Response: DormResource or JSON { message, errors: [] } for delete.
- Dorm admins management:
  - API: POST /api/dorms/{dorm}/create-dorm-admin -> DormController@createDormAdmin.
  - Web: UniversityDormAdminController create/update.
  - Validation: CreateDormAdminRequest, UpdateDormAdminRequest.
  - Creates users.role = DORM_ADMIN + dorm_admins record. Optionally syncs Spatie role.
  - Freeze/unfreeze tracked in user_freeze_logs (web update path).
- Students import/export (Web):
  - UniversityStudentController.
  - Import: StudentImportRequest (file .csv/.txt, max 5MB) + StudentImportService.
  - CSV headers: full_name, student_no, email; optional phone, dorm_code (unless default_dorm_id provided).
  - Generates random password (10 chars) per student.
- Tickets & comments (API v1 + Web):
  - Controllers: Api\V1\TicketController, Web\UniversityTicketController.
  - Validation: TicketRequest + TicketCommentRequest.
  - Scoping: university_id, optional dorm_id must belong to university.
  - Notifications: NotificationService notifies assignee.
- Announcements (API v1 + Web):
  - Controllers: Api\V1\AnnouncementController, Web\UniversityAnnouncementController.
  - Validation: AnnouncementRequest (audience UNIVERSITY/DORM, publish/expire dates).
  - Scoping: dorm announcements require dorm in university.
- Reports/Overview:
  - API: /api/v1/reports/overview.
  - Web: /admin/university/reports.
- Settings / Feature flags:
  - API: /api/v1/settings (GET/POST).
  - Web: /admin/university/settings.
  - Stores flags in system_settings (feature.*) and security.admin_ip_allowlist.
- Audit logs / Activity feed (feature-flagged):
  - API v2: /api/v2/audit-logs, /api/v2/activity-feed.
  - Web: /admin/university/audit-logs, /admin/university/activity-feed.

## DORM_ADMIN capabilities
### Key features
- Floors CRUD (API + Web):
  - Controllers: Api\FloorController, Web\DormFloorController.
  - Validation: FloorRequest (number, bathrooms, kitchens, showers).
  - Scoping: dorm_id from dorm_admins.
- Rooms CRUD + occupancy + assign/unassign (API + Web):
  - Controllers: Api\RoomController, Web\DormRoomController.
  - Validation: RoomRequest (floor_id, room_number unique per dorm, capacity).
  - Assignment: AssignStudentRequest + RoomAssignmentService.
  - Occupants endpoint: /api/rooms/{room}/occupants -> StudentResource collection.
- Students CRUD (API + Web):
  - Controllers: Api\StudentController, Web\DormStudentController.
  - Validation: StudentRequest (full_name, student_no unique, email unique, phone).
  - Creates user with random password; returns generated_password (API) or flash (web).
  - Freeze/unfreeze tracked in user_freeze_logs (web). Soft deletes student and freezes user on archive.
- Assignments view/move (Web): DormAssignmentController lists/moves room assignments.
- Tickets & comments (API v1 + Web):
  - Controllers: Api\V1\TicketController, Web\DormTicketController.
  - Scoping: dorm_id from dorm admin.
- Announcements (API v1 + Web):
  - Controllers: Api\V1\AnnouncementController, Web\DormAnnouncementController.
  - Dorm admins are forced to audience DORM (see AnnouncementRequest::prepareForValidation).

## STUDENT capabilities
- API:
  - GET /api/student/my-room -> StudentRoomController@myRoom.
    - Returns { dorm, floor, room, occupants } or message with nulls if no assignment.
- API (shared auth routes):
  - Announcements index/show (/api/v1/announcements and /api/v1/announcements/{id}) include student-scoped filtering.
- Web: Students are blocked from admin UI (login redirects with error).

# 5) Data Model Map (tables + relationships)
## Core tables (app-specific)
- universities: id, name, code (unique), address, contact_email, contact_phone, is_active, timestamps.
- users: id, name, email (unique), password, role ENUM (UNIVERSITY_ADMIN, DORM_ADMIN, STUDENT, SUPER_ADMIN),
  university_id (FK), is_active, frozen_at, ui_theme, ui_sidebar_collapsed, timestamps.
- dorms: id, university_id (FK), code (nullable), name, address, capacity, status, contact fields, notes, timestamps.
  - Index: (university_id, code).
- dorm_admins: id, user_id (unique FK), dorm_id (FK), timestamps.
- floors: id, dorm_id (FK), number, bathrooms, kitchens, showers, timestamps.
  - Unique: (dorm_id, number).
- rooms: id, dorm_id (FK), floor_id (FK), room_number, capacity, status, timestamps, soft deletes.
  - Unique: (dorm_id, room_number).
- students: id, user_id (unique FK), dorm_id (FK), full_name, student_no (unique), phone, timestamps, soft deletes.
- room_assignments: id, student_id (FK), room_id (FK), active, from_date, to_date, timestamps.
  - Index: (room_id, active), (student_id, active).
  - Unique active assignment per student (partial index or generated column).
- tickets: id, university_id (FK), dorm_id (nullable FK), created_by (FK users), assigned_to (nullable FK users),
  subject, description, category, priority, status, resolved_at, timestamps.
- ticket_comments: id, ticket_id (FK), user_id (FK), body, timestamps.
- announcements: id, university_id (FK), dorm_id (nullable FK), created_by (FK users), title, body, audience, status,
  publish_at, expire_at, timestamps.
- admin_notifications: id, user_id (FK), title, body, link, type, read_at, timestamps.
- system_settings: key (PK), value, type, updated_by (nullable FK users), timestamps.
- audit_logs: id, actor_id (nullable FK users), university_id (nullable FK), action, entity_type, entity_id,
  before_json, after_json, ip, user_agent, correlation_id, created_at.
- record_histories: id, entity_type, entity_id, changes_json, actor_id (nullable FK), university_id (nullable FK), created_at.
- user_freeze_logs: id, user_id (FK), actor_id (nullable FK), action, reason, timestamps.

## Spatie permission tables (optional)
- roles, permissions, model_has_roles, model_has_permissions, role_has_permissions.

## Laravel defaults (present)
- personal_access_tokens (Sanctum), sessions, password_reset_tokens, cache, jobs.

## Relationships (high level)
- University -> Dorms, Users, Tickets, Announcements, AuditLogs.
- Dorm -> Floors -> Rooms -> RoomAssignments.
- Dorm -> Students -> User.
- Dorm -> DormAdmins -> User.
- Ticket -> Comments; Ticket creator/assignee -> User.
- Announcement creator -> User.

## Seeders / bootstrap data
- DatabaseSeeder:
  - Creates Default University (code DEFAULT) + demo users (superadmin@test.com, uniadmin@test.com, dormadmin@test.com, student1@test.com).
  - Seeds dorms, floors, rooms, assigns student, creates sample ticket and announcement.
  - Runs SystemSettingsSeeder + PermissionsSeeder.
- SystemSettingsSeeder: initializes feature flags and settings.
- PermissionsSeeder: seeds permissions and role mappings.
- Migration 2026_01_25_032500_backfill_default_university ensures default university and backfills null university_id.

# 6) Student Onboarding & Authentication Flow
- No self-registration routes found.
- Student creation paths:
  1) Dorm admin creates student:
     - API: POST /api/students (role DORM_ADMIN) -> returns generated_password.
     - Web: POST /admin/dorm/students -> flashes generated_password.
  2) University admin imports students (CSV):
     - Web: POST /admin/university/students/import with file + optional default_dorm_id.
     - StudentImportService generates random 10-char passwords and returns them in import result.
- Credentials delivery:
  - Passwords are generated randomly (10 chars) and returned to admin in API response or web import results.
- Student login:
  - Uses same API endpoint: POST /api/login with email/password.
  - Web login rejects students for admin panel.
- Password reset:
  - No password reset routes/controllers were found in routes/web.php or routes/api.php.

# 7) Error/Response Formats (401/403/422 examples)
- 200 (login success):
  - { "token": "<plainTextToken>", "user": { "id": 1, "university_id": 1, "name": "...", "email": "...", "role": "UNIVERSITY_ADMIN" } }
- 401 (unauthenticated, RoleMiddleware):
  - { "message": "Unauthenticated.", "errors": [] }
- 403 (forbidden, RoleMiddleware):
  - { "message": "Forbidden.", "errors": [] }
- 403 (user frozen, EnsureUserActive):
  - { "message": "Account is inactive.", "errors": [] }
- 404 (feature disabled, FeatureFlagMiddleware):
  - { "message": "Feature not enabled.", "errors": [] }
- 404 (model not found, global handler):
  - { "message": "Resource not found.", "errors": [] }
- 422 (invalid credentials in API login):
  - { "message": "Invalid credentials.", "errors": { "email": ["Invalid credentials."] } }
- 422 (validation errors):
  - Standard Laravel JSON validation shape: { "message": "The given data was invalid.", "errors": { "field": ["..."] } }
- Resource collections:
  - Non-paginated: { "data": [ ... ] }
  - Paginated: { "data": [ ... ], "links": { ... }, "meta": { ... } }

# 8) Open Questions (ONLY if code truly does not reveal)
- None.

