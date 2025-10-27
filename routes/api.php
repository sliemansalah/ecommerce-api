<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ====================================
// Public Routes (بدون Auth)
// ====================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ====================================
// Protected Routes (تحتاج Auth)
// ====================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Users Routes
    Route::apiResource('users', UserController::class);
    Route::get('/users/stats', [UserController::class, 'stats']);
    
    // Permissions Route (يجب أن يكون قبل apiResource)
    Route::get('/permissions', [RoleController::class, 'permissions']);
    
    // Roles Routes
    Route::apiResource('roles', RoleController::class);
    Route::get('/roles/{id}/permissions', [RoleController::class, 'permissions']);
    
});