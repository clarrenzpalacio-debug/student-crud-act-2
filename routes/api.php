<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;

/*
|--------------------------------------------------------------------------
| Public Routes (no authentication required)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (require valid Sanctum token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Logout – any authenticated user
    Route::post('/logout', [AuthController::class, 'logout']);

    // Student routes accessible by any authenticated user (read-only)
    Route::get('/students',       [StudentController::class, 'index']);
    Route::get('/students/{student}', [StudentController::class, 'show']);

    // Student routes restricted to admin role only
    Route::middleware('role:admin')->group(function () {
        Route::post('/students',             [StudentController::class, 'store']);
        Route::put('/students/{student}',    [StudentController::class, 'update']);
        Route::delete('/students/{student}', [StudentController::class, 'destroy']);
    });
});
