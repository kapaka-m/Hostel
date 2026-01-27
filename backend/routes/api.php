<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DormController;
use App\Http\Controllers\Api\FloorController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentRoomController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V2\ActivityFeedController;
use App\Http\Controllers\Api\V2\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:UNIVERSITY_ADMIN')->group(function () {
        Route::apiResource('dorms', DormController::class);
        Route::post('/dorms/{dorm}/create-dorm-admin', [DormController::class, 'createDormAdmin']);
    });

    Route::middleware('role:DORM_ADMIN')->group(function () {
        Route::apiResource('floors', FloorController::class);
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('students', StudentController::class);
        Route::post('/rooms/{room}/assign-student', [RoomController::class, 'assignStudent']);
        Route::get('/rooms/{room}/occupants', [RoomController::class, 'occupants']);
    });

    Route::middleware('role:STUDENT')->group(function () {
        Route::get('/student/my-room', [StudentRoomController::class, 'myRoom']);
    });
});

Route::prefix('v2')->middleware('auth:sanctum')->group(function () {
    Route::middleware(['role:UNIVERSITY_ADMIN', 'feature:audit_logs'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    Route::middleware(['role:UNIVERSITY_ADMIN', 'feature:activity_feed'])->group(function () {
        Route::get('/activity-feed', [ActivityFeedController::class, 'index']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment']);

    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);

    Route::get('/reports/overview', [ReportController::class, 'overview']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);
});
