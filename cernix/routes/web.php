<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\ExaminerWebController;
use App\Http\Controllers\Web\StudentWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));
Route::get('/health', [HealthController::class, 'check']);

// Student portal
Route::get('/student/register',  [StudentWebController::class, 'index']);
Route::post('/student/register', [StudentWebController::class, 'register']);

// Examiner portal
Route::get('/examiner/login',      [ExaminerWebController::class, 'loginForm']);
Route::post('/examiner/login',     [ExaminerWebController::class, 'loginSubmit']);
Route::post('/examiner/logout',    [ExaminerWebController::class, 'logout']);
Route::get('/examiner/dashboard',  [ExaminerWebController::class, 'index']);
Route::post('/examiner/verify',    [ExaminerWebController::class, 'verify']);

// Admin portal
Route::get('/admin/dashboard', [AdminWebController::class, 'index']);
