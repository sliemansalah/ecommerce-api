<?php

use Illuminate\Support\Facades\Route;

// جميع المسارات تعرض نفس الصفحة (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
