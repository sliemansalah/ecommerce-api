<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\AuthController;
use App\Modules\User\Controllers\RoleController;
use App\Modules\User\Controllers\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Users Management (يحتاج صلاحية users.view)
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
    });
    
    Route::middleware('permission:users.create')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });
    
    Route::middleware('permission:users.edit')->group(function () {
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/assign-roles', [UserController::class, 'assignRoles']);
        Route::post('/users/{id}/remove-role', [UserController::class, 'removeRole']);
        Route::post('/users/{id}/assign-permissions', [UserController::class, 'assignPermissions']);
        Route::post('/users/{id}/remove-permission', [UserController::class, 'removePermission']);
    });
    
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
    
    // Roles & Permissions Management
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::get('/permissions', [RoleController::class, 'permissions']);
    });
    
    Route::middleware('permission:users.create')->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
        Route::post('/permissions', [RoleController::class, 'storePermission']);
    });
    
    Route::middleware('permission:users.edit')->group(function () {
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::post('/roles/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
    });
    
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    });


    // Categories Routes
    Route::middleware('permission:categories.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/trashed', [CategoryController::class, 'trashed']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
    });

    Route::middleware('permission:categories.create')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
    });

    Route::middleware('permission:categories.edit')->group(function () {
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::post('/categories/{id}/toggle-active', [CategoryController::class, 'toggleActive']);
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore']);
    });

    Route::middleware('permission:categories.delete')->group(function () {
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        Route::delete('/categories/{id}/force', [CategoryController::class, 'forceDestroy']);
    });

});