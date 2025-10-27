<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

/**
 * Authentication Routes
 * هذه المسارات متاحة للجميع (بدون middleware)
 */
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/**
 * Protected Routes
 * جميع المسارات هنا محمية بـ auth:sanctum
 * يجب على المستخدم تسجيل الدخول أولاً
 */
Route::middleware('auth:sanctum')->group(function () {
    
    // ====================================
    // Auth Routes
    // ====================================
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    
    // ====================================
    // Users Routes
    // ====================================
    // GET    /api/users          -> عرض جميع المستخدمين
    // POST   /api/users          -> إضافة مستخدم جديد
    // GET    /api/users/{id}     -> عرض مستخدم واحد
    // PUT    /api/users/{id}     -> تعديل مستخدم
    // DELETE /api/users/{id}     -> حذف مستخدم
    Route::apiResource('users', UserController::class);
    
    // ====================================
    // Roles Routes
    // ====================================
    // GET    /api/roles          -> عرض جميع الأدوار
    // POST   /api/roles          -> إضافة دور جديد
    // GET    /api/roles/{id}     -> عرض دور واحد
    // PUT    /api/roles/{id}     -> تعديل دور
    // DELETE /api/roles/{id}     -> حذف دور
    Route::apiResource('roles', RoleController::class);
    
    // عرض جميع الصلاحيات (لاستخدامها عند إنشاء/تعديل الأدوار)
    Route::get('permissions', [RoleController::class, 'permissions']);
});
