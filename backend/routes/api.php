<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DormController;
use App\Http\Controllers\Api\FloorController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentRoomController;
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
