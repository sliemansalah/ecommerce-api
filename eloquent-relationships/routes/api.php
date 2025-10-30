<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// مسارات المقالات
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/latest', [PostController::class, 'latest']);
    Route::get('/{post}', [PostController::class, 'show']);
});

// مسارات المستخدمين
Route::get('users/{user}/posts', [PostController::class, 'userPosts']);
Route::get('users', [UserController::class, 'index']);
