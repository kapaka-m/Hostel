<?php

use App\Http\Controllers\Web\AdminHomeController;
use App\Http\Controllers\Web\AdminNotificationController;
use App\Http\Controllers\Web\AdminPreferenceController;
use App\Http\Controllers\Web\AdminSearchController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DormAnnouncementController;
use App\Http\Controllers\Web\DormAssignmentController;
use App\Http\Controllers\Web\DormDashboardController;
use App\Http\Controllers\Web\DormFloorController;
use App\Http\Controllers\Web\DormRoomController;
use App\Http\Controllers\Web\DormStudentController;
use App\Http\Controllers\Web\DormTicketController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\SuperDashboardController;
use App\Http\Controllers\Web\UniversityActivityFeedController;
use App\Http\Controllers\Web\UniversityAnnouncementController;
use App\Http\Controllers\Web\UniversityAuditLogController;
use App\Http\Controllers\Web\UniversityDashboardController;
use App\Http\Controllers\Web\UniversityDormAdminController;
use App\Http\Controllers\Web\UniversityDormController;
use App\Http\Controllers\Web\UniversityReportController;
use App\Http\Controllers\Web\UniversitySettingsController;
use App\Http\Controllers\Web\UniversityStudentController;
use App\Http\Controllers\Web\UniversityTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin.ip'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminHomeController::class, 'index'])->name('home');

    Route::post('/preferences/theme', [AdminPreferenceController::class, 'updateTheme'])->name('preferences.theme');
    Route::post('/notifications/read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::get('/search', [AdminSearchController::class, 'index'])->name('search');

    Route::middleware(['role:SUPER_ADMIN'])->prefix('super')->name('super.')->group(function () {
        Route::get('/', [SuperDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware(['role:UNIVERSITY_ADMIN'])->prefix('university')->name('university.')->group(function () {
        Route::get('/', [UniversityDashboardController::class, 'index'])->name('dashboard');

        Route::get('/activity-feed', [UniversityActivityFeedController::class, 'index'])
            ->middleware('feature:activity_feed')
            ->name('activity-feed.index');
        Route::get('/activity-feed/data', [UniversityActivityFeedController::class, 'data'])
            ->middleware('feature:activity_feed')
            ->name('activity-feed.data');
        Route::get('/audit-logs', [UniversityAuditLogController::class, 'index'])
            ->middleware('feature:audit_logs')
            ->name('audit-logs.index');

        Route::get('/dorms', [UniversityDormController::class, 'index'])->name('dorms.index');
        Route::get('/dorms/create', [UniversityDormController::class, 'create'])->name('dorms.create');
        Route::post('/dorms', [UniversityDormController::class, 'store'])->name('dorms.store');
        Route::get('/dorms/{dorm}/edit', [UniversityDormController::class, 'edit'])->name('dorms.edit');
        Route::put('/dorms/{dorm}', [UniversityDormController::class, 'update'])->name('dorms.update');
        Route::delete('/dorms/{dorm}', [UniversityDormController::class, 'destroy'])->name('dorms.destroy');

        Route::get('/dorm-admins', [UniversityDormAdminController::class, 'index'])->name('dorm-admins.index');
        Route::get('/dorm-admins/create', [UniversityDormAdminController::class, 'create'])->name('dorm-admins.create');
        Route::post('/dorm-admins', [UniversityDormAdminController::class, 'store'])->name('dorm-admins.store');
        Route::get('/dorm-admins/{dormAdmin}/edit', [UniversityDormAdminController::class, 'edit'])->name('dorm-admins.edit');
        Route::put('/dorm-admins/{dormAdmin}', [UniversityDormAdminController::class, 'update'])->name('dorm-admins.update');

        Route::get('/students', [UniversityStudentController::class, 'index'])->name('students.index');
        Route::get('/students/export', [UniversityStudentController::class, 'export'])->name('students.export');
        Route::get('/students/import', [UniversityStudentController::class, 'import'])->name('students.import');
        Route::post('/students/import', [UniversityStudentController::class, 'storeImport'])->name('students.import.store');

        Route::get('/tickets', [UniversityTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/export', [UniversityTicketController::class, 'export'])->name('tickets.export');
        Route::get('/tickets/create', [UniversityTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [UniversityTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [UniversityTicketController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{ticket}/edit', [UniversityTicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [UniversityTicketController::class, 'update'])->name('tickets.update');
        Route::post('/tickets/{ticket}/comment', [UniversityTicketController::class, 'comment'])->name('tickets.comment');

        Route::get('/announcements', [UniversityAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [UniversityAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [UniversityAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}', [UniversityAnnouncementController::class, 'show'])->name('announcements.show');
        Route::get('/announcements/{announcement}/edit', [UniversityAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [UniversityAnnouncementController::class, 'update'])->name('announcements.update');

        Route::get('/reports', [UniversityReportController::class, 'index'])->name('reports.index');

        Route::get('/settings', [UniversitySettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [UniversitySettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['role:DORM_ADMIN'])->prefix('dorm')->name('dorm.')->group(function () {
        Route::get('/', [DormDashboardController::class, 'index'])->name('dashboard');

        Route::get('/floors', [DormFloorController::class, 'index'])->name('floors.index');
        Route::get('/floors/create', [DormFloorController::class, 'create'])->name('floors.create');
        Route::post('/floors', [DormFloorController::class, 'store'])->name('floors.store');
        Route::get('/floors/{floor}/edit', [DormFloorController::class, 'edit'])->name('floors.edit');
        Route::put('/floors/{floor}', [DormFloorController::class, 'update'])->name('floors.update');
        Route::delete('/floors/{floor}', [DormFloorController::class, 'destroy'])->name('floors.destroy');
        Route::post('/floors/bulk-delete', [DormFloorController::class, 'bulkDelete'])->name('floors.bulk-delete');

        Route::get('/rooms', [DormRoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/export', [DormRoomController::class, 'export'])->name('rooms.export');
        Route::get('/rooms/create', [DormRoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [DormRoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}', [DormRoomController::class, 'show'])->name('rooms.show');
        Route::get('/rooms/{room}/edit', [DormRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [DormRoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [DormRoomController::class, 'destroy'])->name('rooms.destroy');
        Route::post('/rooms/{room}/assign', [DormRoomController::class, 'assignStudent'])->name('rooms.assign');
        Route::post('/rooms/{room}/unassign', [DormRoomController::class, 'unassignStudent'])->name('rooms.unassign');
        Route::post('/rooms/{room}/restore', [DormRoomController::class, 'restore'])->name('rooms.restore')->withTrashed();
        Route::post('/rooms/bulk-delete', [DormRoomController::class, 'bulkDelete'])->name('rooms.bulk-delete');
        Route::post('/rooms/bulk-restore', [DormRoomController::class, 'bulkRestore'])->name('rooms.bulk-restore');
        Route::post('/rooms/bulk-export', [DormRoomController::class, 'bulkExport'])->name('rooms.bulk-export');

        Route::get('/students', [DormStudentController::class, 'index'])->name('students.index');
        Route::get('/students/export', [DormStudentController::class, 'export'])->name('students.export');
        Route::get('/students/create', [DormStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [DormStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [DormStudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [DormStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [DormStudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [DormStudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/{student}/unassign', [DormStudentController::class, 'unassign'])->name('students.unassign');
        Route::post('/students/{student}/restore', [DormStudentController::class, 'restore'])->name('students.restore')->withTrashed();
        Route::post('/students/{student}/freeze', [DormStudentController::class, 'freeze'])->name('students.freeze');
        Route::post('/students/{student}/unfreeze', [DormStudentController::class, 'unfreeze'])->name('students.unfreeze');
        Route::post('/students/bulk-delete', [DormStudentController::class, 'bulkDelete'])->name('students.bulk-delete');
        Route::post('/students/bulk-restore', [DormStudentController::class, 'bulkRestore'])->name('students.bulk-restore');
        Route::post('/students/bulk-freeze', [DormStudentController::class, 'bulkFreeze'])->name('students.bulk-freeze');
        Route::post('/students/bulk-unfreeze', [DormStudentController::class, 'bulkUnfreeze'])->name('students.bulk-unfreeze');
        Route::post('/students/bulk-export', [DormStudentController::class, 'bulkExport'])->name('students.bulk-export');

        Route::get('/assignments', [DormAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}/move', [DormAssignmentController::class, 'edit'])->name('assignments.move');
        Route::put('/assignments/{assignment}', [DormAssignmentController::class, 'update'])->name('assignments.update');

        Route::get('/tickets', [DormTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/export', [DormTicketController::class, 'export'])->name('tickets.export');
        Route::get('/tickets/create', [DormTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [DormTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [DormTicketController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{ticket}/edit', [DormTicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [DormTicketController::class, 'update'])->name('tickets.update');
        Route::post('/tickets/{ticket}/comment', [DormTicketController::class, 'comment'])->name('tickets.comment');

        Route::get('/announcements', [DormAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [DormAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [DormAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}', [DormAnnouncementController::class, 'show'])->name('announcements.show');
        Route::get('/announcements/{announcement}/edit', [DormAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [DormAnnouncementController::class, 'update'])->name('announcements.update');
    });
});
