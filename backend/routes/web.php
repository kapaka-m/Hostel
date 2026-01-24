<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DormDashboardController;
use App\Http\Controllers\Web\DormFloorController;
use App\Http\Controllers\Web\DormRoomController;
use App\Http\Controllers\Web\DormStudentController;
use App\Http\Controllers\Web\UniversityActivityFeedController;
use App\Http\Controllers\Web\UniversityAuditLogController;
use App\Http\Controllers\Web\UniversityDashboardController;
use App\Http\Controllers\Web\UniversityDormAdminController;
use App\Http\Controllers\Web\UniversityDormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:UNIVERSITY_ADMIN', 'admin.ip'])->prefix('admin/university')->name('admin.university.')->group(function () {
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

    Route::get('/dorm-admins/create', [UniversityDormAdminController::class, 'create'])->name('dorm-admins.create');
    Route::post('/dorm-admins', [UniversityDormAdminController::class, 'store'])->name('dorm-admins.store');
});

Route::middleware(['auth', 'role:DORM_ADMIN', 'admin.ip'])->prefix('admin/dorm')->name('admin.dorm.')->group(function () {
    Route::get('/', [DormDashboardController::class, 'index'])->name('dashboard');

    Route::get('/floors', [DormFloorController::class, 'index'])->name('floors.index');
    Route::get('/floors/create', [DormFloorController::class, 'create'])->name('floors.create');
    Route::post('/floors', [DormFloorController::class, 'store'])->name('floors.store');
    Route::get('/floors/{floor}/edit', [DormFloorController::class, 'edit'])->name('floors.edit');
    Route::put('/floors/{floor}', [DormFloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [DormFloorController::class, 'destroy'])->name('floors.destroy');

    Route::get('/rooms', [DormRoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [DormRoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [DormRoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [DormRoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [DormRoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [DormRoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [DormRoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{room}/assign', [DormRoomController::class, 'assignStudent'])->name('rooms.assign');

    Route::get('/students', [DormStudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [DormStudentController::class, 'create'])->name('students.create');
    Route::post('/students', [DormStudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [DormStudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [DormStudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [DormStudentController::class, 'destroy'])->name('students.destroy');
});
