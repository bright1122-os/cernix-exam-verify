<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Examiner\AuthController as ExaminerAuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use Illuminate\Support\Facades\Route;

// Student auth
Route::prefix('student')->group(function () {
    Route::post('register', [StudentAuthController::class, 'register']);
    Route::post('login', [StudentAuthController::class, 'login']);
});

// Examiner auth
Route::prefix('examiner')->group(function () {
    Route::post('login', [ExaminerAuthController::class, 'login']);
});

// Admin auth
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
});

// Shared protected routes (any authenticated role)
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});
